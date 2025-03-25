<?php

namespace HCC\Plugin\Core\Container\Interfaces;

interface ServiceProviderInterface
{
    public function register(ContainerInterface $container): void;
}
