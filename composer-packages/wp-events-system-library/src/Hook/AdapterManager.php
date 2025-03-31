<?php

namespace HCC\Events\Hook;

use HCC\Events\Hook\Interfaces\EventAdapterInterface;

class AdapterManager
{
    private array $adapters = [];

    public function addAdapter(string $adapterName, EventAdapterInterface $adapter): void
    {
        $this->adapters[$adapterName] = $adapter;
    }

    public function getAdapters(): array
    {
        return $this->adapters;
    }

    public function dispatchAdapter(string $event, string $adapterName, array $args = []): void
    {
        try {
            if (!$this->adapters[$adapterName]) {
                throw new \InvalidArgumentException("Adapter {$adapterName} not found");
            }

            $this->adapters[$adapterName]->dispatch($event, $args);
        } catch (\InvalidArgumentException $exception) {
            $this->handleException($exception->getMessage());
        }
    }

    public function dispatch(string $event, array $args = [], ?string $adapterKey = null): array
    {

    }

    protected function handleException(string $message)
    {
        throw new \RuntimeException($message);
    }
}