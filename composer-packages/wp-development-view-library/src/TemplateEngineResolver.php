<?php

namespace HCC\View;

use HCC\View\Storage\StorageFacade;
use HCC\View\Engine\BladeEngine;
use HCC\View\Engine\PhpEngine;
use HCC\View\Engine\TwigEngine;
use HCC\View\Interfaces\TemplateEngineInterface;
use HCC\View\Interfaces\TemplateResolverInterface;
use HCC\View\Interfaces\TemplateCacheInterface;

/**
 * With TemplateEngineInterface classes this resolver implements Strategy pattern
 */
class TemplateEngineResolver implements TemplateResolverInterface
{
    private StorageFacade $storage;
    private TemplateCacheInterface $cache;

    public function __construct(string $group, TemplateCacheInterface $cache)
    {
        $this->storage = StorageFacade::for($group);
        $this->cache = $cache;
    }

    public function resolve(string $template, string $path): TemplateEngineInterface
    {
        if (str_ends_with($template, '.twig')) {
            if (!class_exists('Twig\Environment')) {
                throw new \RuntimeException('Twig is not installed');
            }

            return $this->storage->remember(
                'engine.twig',
                fn() => new TwigEngine(basename($path), $this->cache)
            );
        }

        if (str_ends_with($template, '.blade.php')) {
            $classes = [
                '\Illuminate\Filesystem\Filesystem',
                '\Illuminate\View\Compilers\BladeCompiler',
                '\Illuminate\View\FileViewFinder',
                '\Illuminate\View\Engines\CompilerEngine',
                '\Illuminate\View\Factory'
            ];

            if (count($classes) !== count(array_filter(array_map(fn(string $class) => class_exists($class), $classes)))) {
                throw new \RuntimeException('Blade is not installed');
            }

            return $this->storage->remember(
                'engine.blade',
                fn() => new BladeEngine(basename($path), $this->cache)
            );
        }

        return $this->storage->remember('engine.php', fn() => new PhpEngine($this->cache));
    }
}