<?php

namespace HCC\Events;

use HCC\Events\Interfaces\EventDispatcherInterface;

class DispatcherFactory
{
    public static function create(string $mechanism = 'wordpress'): EventDispatcherInterface
    {
        return match ($mechanism) {
            'wordpress' => new Hook\WordPressEventDispatcher(),
            'rxphp' => new RxPHP\RxPHPEventDispatcher(),
            'symfony' => new SymfonyEventDispatcher(),
            default => throw new \InvalidArgumentException("Невідомий механізм: $mechanism"),
        };
    }
}
