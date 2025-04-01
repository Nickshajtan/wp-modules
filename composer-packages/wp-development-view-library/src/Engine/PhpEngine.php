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
        $cacheFile = $this->cache->get($basename);
        if ($cacheFile) {
            return $cacheFile;
        }

        ob_start();
        extract($data);
        include $path;

        $content = ob_get_clean();
        $this->cache->set($basename, $content);

        return $content;
    }
}
