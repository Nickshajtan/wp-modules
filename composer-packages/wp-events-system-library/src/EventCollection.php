<?php

namespace HCC\Events;

use HCC\Events\Interfaces\CollectionInterface;

/**
 * Class that stores module hooks in local state and accesses the global callback only when necessary
 */
class EventCollection implements CollectionInterface // ??? Setup to ommit global callbacks as well
{
    private array $eventsById = [];
    private array $eventIdsByName = [];

    private CollectionCallbacksStore $handlers;

    public function __construct(CollectionCallbacksStore $handlers)
    {
        $this->handlers = $handlers;
    }

    public function getAllEvents(string $eventName = '', int $priority = self::DEFAULT_PRIORITY): array
    {
        return empty($eventName) ? $this->eventsById : $this->filterEvents($eventName, $priority);
    }

    public function addEvent(
        string          $eventName,
        callable        $callback,
        int             $priority = self::DEFAULT_PRIORITY,
        int             $acceptedArgs = 1,
        ?object         $object = null,
        null|string|int $id = null
    ): void
    {
        $id = $id ?
            $this->encodeId($id) : $this->generateEventId(eventName: $eventName, callback: $callback, priority: $priority);
        $this->eventsById[$id] = new Event(
            eventName: $eventName,
            callback: fn(...$args) => $callback(...array_slice($args, 0, $acceptedArgs)),
            priority: $priority,
            acceptedArgs: $acceptedArgs,
            component: $object,
            id: $id
        );
        $this->eventIdsByName[$eventName][$priority][$id] = $id;
    }

    public function removeEvent(
        string    $eventName,
        string    $id = '',
        int       $priority = self::DEFAULT_PRIORITY,
        ?callable $callback = null
    ): bool
    {
        if (empty($id)) {
            $id = 1 !== count($this->eventIdsByName[$eventName][$priority] ?? []) ?
                $this->generateEventId(eventName: $eventName, callback: $callback, priority: $priority) :
                (string) array_key_first($this->eventIdsByName[$eventName][$priority] ?? []);
        }

        if (!empty($this->findEventById($id))) {
            unset($this->eventsById[$id]);

            if (!empty($this->eventIdsByName[$eventName][$priority][$id])) {
                unset($this->eventIdsByName[$eventName][$priority][$id]);
            }

            return true;
        }

        return false;
    }

    public function registerEvent(string $eventName, string $id = '', ?int $priority = null): void
    {
        $hook = $this->findEventById($id);
        // ???
        if (!is_null($hook)) {
            $this->callEvent($this->handlers->add, $hook->toArray());
        }
    }

    public function deregisterEvent(string $eventName, string $id = '', ?int $priority = null): void
    {
        if (!$this->removeEvent(eventName: $eventName, id: $id, priority: $priority)) {
            $this->callEvent($this->handlers->remove, [$eventName, $priority]);
        }
    }

    public function dispatchEvent(string $eventName, string $id = '', ...$args): mixed
    {
        $hook = $this->findEventById($id);
        // ???
        if (!is_null($hook)) {
            return $hook->dispatch(...$args);
        }

        return $this->callEvent($this->handlers->execute, [$eventName, ...$args]);
    }

    protected function findEventById(string $id): ?Event
    {
        return $this->eventsById[$id] ?? null;
    }
    protected function filterEvents(string $hookName, int $priority = self::DEFAULT_PRIORITY): array
    {
        $filteredEvents = array_filter(array_map(
            fn(string $id) => $this->findEventById($id),
            $this->eventIdsByName[$hookName][$priority] ?? []
        ));
        if (count($filteredEvents) < 1) {
            return [];
        }

        if (count($filteredEvents) > 1) {
            usort($filteredEvents, fn($a, $b) => ($a->priority ?? 0) <=> ($b->priority ?? 0));
        }

        return array_values($filteredEvents);
    }

    protected function callEvent(callable $handler, array $args): mixed
    {
        try {
            return call_user_func_array($handler, $args);
        } catch (\Exception $exception) {
            $this->handleException($exception->getMessage());
        }
    }

    protected function generateEventId(string $eventName, array|object|string $callback, ?int $priority = null): string
    {
        static $closureCache = [];

        try {
            $getStableClosureHash = function (\Closure $closure) use (&$closureCache): string {
                $hash = spl_object_hash($closure);
                if (isset($closureCache[$hash])) {
                    return $closureCache[$hash];
                }

                $reflection = new \ReflectionFunction($closure);
                $vars = $reflection->getStaticVariables();
                $fileName = $reflection->getFileName();
                $code = $fileName ? file($fileName) : [];
                $codeSnippet = trim(implode("\n", array_slice($code, $reflection->getStartLine() - 1, 5)));

                $id = $this->encodeId(serialize(['code' => $codeSnippet, 'vars' => $vars]));
                $closureCache[$hash] = $id;

                return $id;
            };
            $callbackId = match (true) {
                is_array($callback) && is_object($callback[0]) => get_class($callback[0]) . '::' . $callback[1],
                is_array($callback) => implode(
                    '|',
                    array_map(fn($item) => str_replace('::', '%double_colon%', (string)$item), $callback)
                ),
                is_string($callback) => $callback,
                $callback instanceof \Closure => $getStableClosureHash($callback),
                is_object($callback) => get_class($callback) . '@' . $this->encodeId(serialize($callback)),
                default => spl_object_hash($callback),
            };
            $toId = $eventName . '|' . $callbackId;
            if (!is_null($priority)) {
                $toId .= '|' . $priority;
            }

            return $this->encodeId($toId);
        } catch (\Exception $exception) {
            $this->handleException($exception->getMessage());
        }
    }

    protected function encodeId(string $id): string
    {
        return hash('sha256', $id);
    }

    protected function handleException(string $message)
    {
        throw new \RuntimeException($message);
    }
}
