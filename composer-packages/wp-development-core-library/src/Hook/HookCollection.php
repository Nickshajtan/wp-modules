<?php

namespace HCC\Core\Hook;

use HCC\Core\Hook\Interfaces\CollectionInterface;

/**
 * Class that stores module hooks in local state and accesses the global callback only when necessary
 */
class HookCollection implements CollectionInterface
{
    private array $hooks = array();

    private CallbacksStore $handlers;

    public function __construct(CallbacksStore $handlers)
    {
        $this->handlers = $handlers;
    }

    public function getAllHooks(string $hookName = '', ?int $priority = null): array
    {
        return $this->filterHooks($hookName, $priority);
    }

    public function addHook(
        string          $hookName,
        callable        $callback,
        int             $priority = 10,
        int             $acceptedArgs = 1,
        ?object         $object = null,
        null|string|int $id = null
    ): void
    {
        $id = $id ?
            $this->encodeId($id) : $this->generateHookId(hookName: $hookName, callback: $callback, priority: $priority);
        $this->hooks[$id] = new Hook(
            hookName: $hookName,
            callback: fn(...$args) => $callback(...array_slice($args, 0, $acceptedArgs)),
            priority: $priority,
            acceptedArgs: $acceptedArgs,
            component: $object,
            id: $id
        );
    }

    public function removeHook(string $hookName, string $id = '', ?int $priority = null): bool
    {
        $hook = $this->findHook($hookName);
        if (!is_null($hook)) {
            $this->hooks = array_values(array_diff($this->hooks, [$hook]));
            return true;
        }

        return false;
    }

    public function registerHook(string $hookName, string $id = '', ?int $priority = null): void
    {
        $hook = $this->findHook($id);
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

    public function dispatchHook(string $hookName, string $id = '', ?int $priority = null, ...$args): mixed
    {
        $hook = $this->findHook($id);
        return $this->callHook($this->handlers->execute, [$hook ? $hook->hookName : $hookName, ...$args]);
    }

    protected function findHook(string $id): ?Hook
    {
        return $this->hooks[$id] ?? null;
    }

    protected function filterHooks(string $hookName, ?int $priority = null): array
    {
        $filteredHooks = $this->hooks;
        if (!empty($hookName)) {
            $filteredHooks = array_filter($filteredHooks, fn(Hook $hook) => $hook->hookName === $hookName);
        }

        if (!is_null($priority)) {
            $filteredHooks = array_filter($filteredHooks, fn(Hook $hook) => $hook->priority === $priority);
        }

        if (is_null($priority) && count($filteredHooks) > 1) {
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
        try {
            $getStableClosureHash = function (\Closure $closure): string {
                $reflection = new \ReflectionFunction($closure);
                $vars = $reflection->getStaticVariables();
                $code = file($reflection->getFileName());
                $codeSnippet = trim(implode("\n", array_slice($code, $reflection->getStartLine() - 1, 5)));

                return $this->encodeId(
                    serialize([
                        'code' => $codeSnippet,
                        'vars' => $vars,
                    ])
                );
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
