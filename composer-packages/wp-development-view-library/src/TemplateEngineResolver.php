<?php

namespace HCC\View;

use HCC\View\Engine\BladeEngine;
use HCC\View\Engine\PhpEngine;
use HCC\View\Engine\TwigEngine;
use HCC\View\Interfaces\TemplateEngineInterface;
use HCC\View\Interfaces\TemplateResolverInterface;
use http\Exception\RuntimeException;

/**
 * With TemplateEngineInterface classes this resolver implements Strategy pattern
 */
class TemplateEngineResolver implements TemplateResolverInterface
{
    public function resolve(string $template, string $path, string $cachePath): TemplateEngineInterface
    {
        $cachePath = rtrim($cachePath, DIRECTORY_SEPARATOR);

        if (str_ends_with($template, '.twig')) {
            if (!class_exists('Twig\Environment')) {
                throw new RuntimeException('Twig is not installed');
            }

            return new TwigEngine(basename($path), $cachePath);
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
                throw new RuntimeException('Blade is not installed');
            }

            return new BladeEngine(basename($path), $cachePath);
        }

        return new PhpEngine($cachePath);
    }
}