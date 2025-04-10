<?php

namespace HCC\Attributes\Attribute;

use HCC\Attributes\Interfaces\AttributeInterface;
use \Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Decorator implements AttributeInterface
{
    public $callable;

    public function __construct(string|callable $callable)
    {
        $this->callable = $callable;
    }
}
