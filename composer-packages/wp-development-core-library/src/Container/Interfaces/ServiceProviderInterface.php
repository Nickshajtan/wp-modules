<?php

namespace HCC\Core\Container\Interfaces;

interface ServiceProviderInterface
{
    public function register(ContainerInterface $container): void;
}
