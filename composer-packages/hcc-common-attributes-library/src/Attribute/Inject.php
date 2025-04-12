<?php

namespace HCC\Attributes\Attribute;

use HCC\Attributes\Interfaces\AttributeInterface;
use \Attribute;

/**
 * #[Inject(service: Mailer::class, lazy: true)]
 * private $lazyMailer;
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Inject implements AttributeInterface
{
    public function __construct(public ?string $service = null, public bool $lazy = true){}
}
