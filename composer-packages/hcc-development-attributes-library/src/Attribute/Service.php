<?php

namespace HCC\Attributes\Attribute;

use HCC\Attributes\Interfaces\AttributeInterface;
use \Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Service implements AttributeInterface
{
    private bool $singleton;
    private string $name;

    public function __construct(bool $singleton = true, string $name = '')
    {
        $this->singleton = $singleton;
        $this->name = $name;
    }

    public function isSingleton(): bool {
        return $this->singleton;
    }

    public function getName(): string {
        return $this->name;
    }
}
