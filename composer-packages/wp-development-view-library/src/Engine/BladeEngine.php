<?php

namespace HCC\View\Engine;

use HCC\View\Interfaces\TemplateEngineInterface;
use \Illuminate\Filesystem\Filesystem;
use \Illuminate\View\Compilers\BladeCompiler;
use \Illuminate\View\FileViewFinder;
use \Illuminate\View\Engines\CompilerEngine;
use \Illuminate\View\Factory;

class BladeEngine implements TemplateEngineInterface
{
    protected string $cachePath;
    protected Factory $viewFactory;

    public function __construct(string $path, string $cachePath)
    {
        $this->cachePath = $cachePath;
        $filesystem = new Filesystem();
        $compiler = new BladeCompiler($filesystem, $this->cachePath);
        $viewFinder = new FileViewFinder($filesystem, [$path]);
        $this->viewFactory = new Factory(new CompilerEngine($compiler), $viewFinder);
    }
    public function render(string $path, array $data = []): string
    {
        return $this->viewFactory->make($path, $data)->render();
    }
}