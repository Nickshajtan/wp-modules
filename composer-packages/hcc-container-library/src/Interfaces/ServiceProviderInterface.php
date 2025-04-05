<?php

namespace HCC\Container\Interfaces;

interface ServiceProviderInterface
{
    public function register(ContainerInterface $container): void;
}
