<?php

namespace HCC\Cache;

use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;
use Psr\Cache\CacheItemPoolInterface as Psr6CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class CacheManager
{
    protected array $drivers = [];
    protected string $default;

    public function __construct(array $config = [])
    {
        $this->default = $config['default'] ?? 'file';
        $this->registerDefaultDrivers($config['stores'] ?? []);
    }

    protected function registerDefaultDrivers(array $stores): void
    {
        foreach ($stores as $name => $driver) {
            $this->drivers[$name] = $driver;
        }

        if (!isset($this->drivers['file'])) {
            $this->drivers['file'] = new FilesystemAdapter();
        }
        if (!isset($this->drivers['redis']) && class_exists(RedisAdapter::class)) {
            $this->drivers['redis'] = new RedisAdapter(RedisAdapter::createConnection('redis://localhost'));
        }
        if (!isset($this->drivers['apcu']) && ApcuAdapter::isSupported()) {
            $this->drivers['apcu'] = new ApcuAdapter();
        }
        if (!isset($this->drivers['memory'])) {
            $this->drivers['memory'] = new ArrayAdapter();
        }
    }

    public function getDriver(string $name = null)
    {
        $name = $name ?? $this->default;

        return $this->drivers[$name] ?? throw new \InvalidArgumentException("Cache driver [$name] not found.");
    }

    public function psr6(string $driver = null): Psr6CacheInterface
    {
        $instance = $this->getDriver($driver);

        if ($instance instanceof Psr6CacheInterface) {
            return $instance;
        }

        throw new \RuntimeException("Driver does not implement PSR-6: " . get_class($instance));
    }

    public function psr16(string $driver = null): Psr16CacheInterface
    {
        $instance = $this->getDriver($driver);

        if ($instance instanceof Psr16CacheInterface) {
            return $instance;
        }

        if ($instance instanceof Psr6CacheInterface) {
            return new Psr16Cache($instance);
        }

        throw new \RuntimeException("Driver is not PSR-16 compatible: " . get_class($instance));
    }
}
