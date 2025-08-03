<?php

namespace HCC\View\Engine;

use HCC\View\Interfaces\TemplateEngineInterface;
use HCC\View\Interfaces\TemplateCacheInterface;

class PhpEngine implements TemplateEngineInterface
{
    protected TemplateCacheInterface $cache;

    public function __construct(TemplateCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function render(string $path, array $data): string
    {
        if (empty($path)) {
            return "<!-- Template {$path} not found -->";
        }

        $basename = basename($path);
        $cachedOutput = $this->cache->get(filename: $basename, context: $data);
        if (!empty($cachedOutput)) {
            return $cachedOutput;
        }

        ob_start();
        extract($data);
        include $path;

        $content = ob_get_clean();
        $this->cache->set(filename: $basename, content: $content, context: $data);

        return $content;
    }
}
