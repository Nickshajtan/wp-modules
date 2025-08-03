<?php

namespace HCC\View\Engine;

use HCC\View\Interfaces\TemplateEngineInterface;
use HCC\View\Interfaces\TemplateCacheInterface;
use \Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\EngineResolver;
use \Illuminate\View\Compilers\BladeCompiler;
use \Illuminate\View\FileViewFinder;
use \Illuminate\View\Engines\CompilerEngine;
use Illuminate\Events\Dispatcher;
use \Illuminate\View\Factory;

class BladeEngine implements TemplateEngineInterface
{
    protected Factory $viewFactory;
    protected string $path;

    public function __construct(string $path, ?TemplateCacheInterface $cache = null)
    {
        $this->path = preg_replace('#[\\\\/]+#', DIRECTORY_SEPARATOR, $path);
        $cacheDir = $cache ? $cache->getCacheDirectory() : $path . DIRECTORY_SEPARATOR . 'cache';
        $filesystem = new Filesystem();
        $compiler = new BladeCompiler($filesystem, $cacheDir);
        $resolver = new EngineResolver();
        $resolver->register('blade', fn() => new CompilerEngine($compiler) );
        $this->viewFactory = new Factory( $resolver, new FileViewFinder($filesystem, [$path]), new Dispatcher());
        $this->viewFactory->addExtension('blade.php', 'blade');
    }
    public function render(string $path, array $data = []): string
    {
        $path = str_replace(['.blade.php', '.blade'], ['', ''], $path);
        if ( str_starts_with($path, $this->path) ) {
            $path = str_replace($this->path, '', $path);
        }

        $path = trim($path, DIRECTORY_SEPARATOR);

        return $this->viewFactory->make($path, $data)->render();
    }
}