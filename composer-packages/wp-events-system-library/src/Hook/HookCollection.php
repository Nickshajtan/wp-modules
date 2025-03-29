<?php

namespace HCC\Events\Hook;

use HCC\Events\Hook\Interfaces\CollectionInterface;

/**
 * Class that stores module hooks in local state and accesses the global callback only when necessary
 */
class HookCollection implements CollectionInterface
{
    private array $hooksById = [];
    private array $hookIdsByName = [];

    private CallbacksStore $handlers;

    public function __construct(CallbacksStore $handlers)
    {
        $this->handlers = $handlers;
    }

    public function getAllHooks(string $hookName = '', int $priority = self::DEFAULT_PRIORITY): array
    {
        return empty($hookName) ? $this->hooksById : $this->filterHooks($hookName, $priority);
    }

    public function addHook(
        string          $hookName,
        callable        $callback,
        int             $priority = self::DEFAULT_PRIORITY,
        int             $acceptedArgs = 1,
        ?object         $object = null,
        null|string|int $id = null
    ): void
    {
        $id = $id ?
            $this->encodeId($id) : $this->generateHookId(hookName: $hookName, callback: $callback, priority: $priority);
        $this->hooksById[$id] = new Hook(
            hookName: $hookName,
            callback: fn(...$args) => $callback(...array_slice($args, 0, $acceptedArgs)),
            priority: $priority,
            acceptedArgs: $acceptedArgs,
            component: $object,
            id: $id
        );
        $this->hookIdsByName[$hookName][$priority][$id] = $id;
    }

    public function removeHook(
        string    $hookName,
        string    $id = '',
        int       $priority = self::DEFAULT_PRIORITY,
        ?callable $callback = null
    ): bool
    {
        if (empty($id)) {
            $id = 1 !== count($this->hookIdsByName[$hookName][$priority] ?? []) ?
                $this->generateHookId(hookName: $hookName, callback: $callback, priority: $priority) :
                (string) array_key_first($this->hookIdsByName[$hookName][$priority] ?? []);
        }

        if (!empty($this->findHookById($id))) {
            unset($this->hooksById[$id]);

            if (!empty($this->hookIdsByName[$hookName][$priority][$id])) {
                unset($this->hookIdsByName[$hookName][$priority][$id]);
            }

            return true;
        }

        return false;
    }

    public function registerHook(string $hookName, string $id = '', ?int $priority = null): void
    {
        $hook = $this->findHookById($id);
        if (!is_null($hook)) {
            $this->callHook($this->handlers->add, $hook->toArray());
        }
    }

    public function deregisterHook(string $hookName, string $id = '', ?int $priority = null): void
    {
        if (!$this->removeHook($hookName, $id, $priority)) {
            $this->callHook($this->handlers->remove, [$hookName, $priority]);
        }
    }

    public function dispatchHook(string $hookName, string $id = '', ...$args): mixed
    {
        $hook = $this->findHookById($id);
        if (!is_null($hook)) {
            return $hook->dispatch(...$args);
        }

        return $this->callHook($this->handlers->execute, [$hookName, ...$args]);
    }

    protected function findHookById(string $id): ?Hook
    {
        return $this->hooksById[$id] ?? null;
    }
    protected function filterHooks(string $hookName, int $priority = self::DEFAULT_PRIORITY): array
    {
        $filteredHooks = array_filter(array_map(
            fn(string $id) => $this->findHookById($id),
            $this->hookIdsByName[$hookName][$priority] ?? []
        ));
        if (count($filteredHooks) < 1) {
            return [];
        }

        if (count($filteredHooks) > 1) {
            usort($filteredHooks, fn($a, $b) => ($a->priority ?? 0) <=> ($b->priority ?? 0));
        }

        return array_values($filteredHooks);
    }

    protected function callHook(callable $handler, array $args): mixed
    {
        try {
            return call_user_func_array($handler, $args);
        } catch (\Exception $exception) {
            $this->handleException($exception->getMessage());
        }
    }

    protected function generateHookId(string $hookName, array|object|string $callback, ?int $priority = null): string
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
            $toId = $hookName . '|' . $callbackId;
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
