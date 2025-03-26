<?php

namespace HCC\Core\Container\Interfaces;

interface ServiceConsumerInterface
{
    public function getService(string $serviceName);
    public function execute(): void;
}
