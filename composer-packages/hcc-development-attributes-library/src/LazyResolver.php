<?php

namespace HCC\Attributes;

use HCC\Attributes\Interfaces\ResolverInterface;
use HCC\Container\Interfaces\ContainerInterface;

class LazyResolver implements ResolverInterface
{
    protected ContainerInterface $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function resolve(string|object $service, bool $lazy): mixed
    {
        if ($lazy) {
            return fn() => $this->container->get($service);
        }

        return $this->container->get($service);
    }
}
