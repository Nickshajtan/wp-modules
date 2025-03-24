<?php

namespace HCC\Plugin\Core\Hook;

use HCC\Plugin\Core\Hook\Interfaces\CollectionInterface;

class HookCollection implements CollectionInterface
{
    private array $hooks = array();

    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
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
        $this->hooks[] = new Hook(
            hookName: $hookName,
            callback: fn(...$args) => call_user_func_array($callback, array_slice($args, 0, $acceptedArgs)),
            priority: $priority,
            acceptedArgs: $acceptedArgs,
            component: $object,
            id: $id
        );
    }

    public function removeHook(string $hookName, string $id = '', ?int $priority = null): void
    {
        $hook = $this->findHook($hookName, $id, $priority);
        if (!is_null($hook)) {
            $this->hooks = array_values(array_diff($this->hooks, [$hook]));
        }
    }

    public function executeHook(string $hookName, string $id = '', ?int $priority = null): mixed
    {
        $hook = $this->findHook($hookName, $id, $priority);
        if (is_null($hook)) {
            $this->handleException("Hook {$hookName}: {$id} not found.");
        }

        return $this->callHook($hook);
    }

    public function callHook(Hook $hook): mixed
    {
        if (!is_callable($this->handler)) {
            $this->handleException("Handler for hook {$hook->hookName} is not callable.");
        }

        if (!is_callable($hook->callback)) {
            $this->handleException("Callback for hook {$hook->hookName} is not callable.");
        }

        return call_user_func_array($this->handler, array_values((array) $hook));
    }

    protected function findHook(string $hookName = '', string $id = '', ?int $priority = null): ?Hook
    {
        if (empty($hookName) && empty($id)) {
            return null;
        }

        if (!empty($id)) {
            $key = array_search($id, array_column($this->hooks, 'id'), true);
            if ($key) {
                return $this->hooks[$key];
            }
        }

        return current($this->filterHooks($hookName, $priority));
    }

    protected function filterHooks(string $hookName, ?int $priority = null): array
    {
        $filteredHooks = $this->hooks;
        if (!empty($hookName)) {
            $filteredHooks = array_filter($filteredHooks, function (Hook $hook) use ($hookName, $priority) {
                return ($hook->hookName ?? '') === $hookName && (is_null($priority) || ($hook->priority ?? 0) === $priority);
            });
        }

        if (is_null($priority)) {
            usort($filteredHooks, fn($a, $b) => ($a->priority ?? 0) <=> ($b->priority ?? 0));
        }

        return $filteredHooks;
    }

    protected function handleException(string $message)
    {
        throw new \RuntimeException($message);
    }
}
