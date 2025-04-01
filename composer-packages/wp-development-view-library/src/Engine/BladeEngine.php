<?php

namespace HCC\View\Engine;

use HCC\View\Interfaces\TemplateEngineInterface;
use HCC\View\Interfaces\TemplateCacheInterface;
use \Illuminate\Filesystem\Filesystem;
use \Illuminate\View\Compilers\BladeCompiler;
use \Illuminate\View\FileViewFinder;
use \Illuminate\View\Engines\CompilerEngine;
use \Illuminate\View\Factory;

class BladeEngine implements TemplateEngineInterface
{
    protected Factory $viewFactory;

    public function __construct(string $path, ?TemplateCacheInterface $cache = null)
    {
        $cacheDir = $cache ? $cache->getCacheDirectory() : dirname($path);
        $filesystem = new Filesystem();
        $compiler = new BladeCompiler($filesystem, $cacheDir);
        $viewFinder = new FileViewFinder($filesystem, [$path]);
        $this->viewFactory = new Factory(new CompilerEngine($compiler), $viewFinder);
    }
    public function render(string $path, array $data = []): string
    {
        return $this->viewFactory->make($path, $data)->render();
    }
}