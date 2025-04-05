<?php

namespace HCC\View\Engine;

use HCC\View\Interfaces\TemplateCacheInterface;
use HCC\View\Interfaces\TemplateEngineInterface;
use \Twig\Environment;
use \Twig\Loader\FilesystemLoader;

class TwigEngine implements TemplateEngineInterface
{
    protected Environment $twig;

    public function __construct(string $path, ?TemplateCacheInterface $cache = null)
    {
        $this->twig = new Environment(
            new FilesystemLoader($path),
            [
                'cache' => $cache ?? dirname($path),
            ]
        );
    }

    public function render(string $path, array $data): string
    {
        return $this->twig->render($path, $data);
    }
}