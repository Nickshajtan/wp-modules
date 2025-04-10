<?php

namespace HCC\Attributes\Interfaces;

interface ResolverInterface
{
    public function resolve(string|object $service, bool $lazy): mixed;
}
