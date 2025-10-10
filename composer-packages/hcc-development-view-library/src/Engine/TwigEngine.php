<?php

namespace HCC\View\Engine;

use HCC\View\Interfaces\TemplateEngineInterface;
use \Twig\Environment;
use \Twig\Loader\FilesystemLoader;
use HCC\View\Cache\TwigCacheAdapter;

class TwigEngine implements TemplateEngineInterface
{
    protected Environment $twig;
    protected string $path;

    public function __construct(string $path, ?TwigCacheAdapter $cache = null)
    {
        $this->path = preg_replace('#[\\\\/]+#', DIRECTORY_SEPARATOR, $path);
        $this->twig = new Environment(
            new FilesystemLoader($this->path),
            [
                'cache' => $cache ?? dirname($this->path) . DIRECTORY_SEPARATOR . 'cache',
            ]
        );
    }

    public function render(string $path, array $data): string
    {
        if ( str_starts_with($path, $this->path) ) {
            $path = str_replace($this->path, '', $path);
        }

        $path = trim($path, DIRECTORY_SEPARATOR);

        return $this->twig->render($path, $data);
    }
}