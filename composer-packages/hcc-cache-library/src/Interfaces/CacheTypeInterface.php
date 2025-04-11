<?php

namespace HCC\Cache\Interfaces;

interface CacheTypeInterface
{
    public function makeInstance(): CacheDriverInterface;
    public function value(): string;

    public function getDriver(): string;
}
