<?php

namespace HCC\Attributes\Attribute;

use HCC\Attributes\Interfaces\AttributeInterface;
use \Attribute;

/**
 * Eg #[Configurable(source: 'env', prefix: 'APP_')]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Configurable implements AttributeInterface
{
    public function __construct(public string $source = 'env', public ?string $prefix = null) {}
}
