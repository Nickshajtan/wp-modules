<?php

namespace HCC\View\Engine;

use HCC\View\Interfaces\TemplateEngineInterface;

class PhpEngine implements TemplateEngineInterface
{
    protected string $cachePath;
    protected int $cacheLifetime;

    public function __construct(string $cachePath, int $cacheLifetime = 3600)
    {
        $this->cachePath = $cachePath;
        $this->cacheLifetime = $cacheLifetime;
    }

    public function render(string $path, array $data): string
    {
        if (empty($path)) {
            return "<!-- Template {$path} not found -->";
        }

        $cacheFile = $this->getCacheFileName($path);
        if ($this->checkCachedFile($cacheFile)) {
            return $this->getCachedContent($cacheFile);
        }

        ob_start();
        extract($data);
        include $path;

        $content = ob_get_clean();
        $this->setCachedContent($cacheFile, $content);

        return $content;
    }

    protected function setCachedContent(string $cacheFile, string $content): void
    {
        file_put_contents($cacheFile, $content);
    }

    protected function getCachedContent(string $cacheFile): string
    {
        return file_get_contents($cacheFile);
    }

    protected function checkCachedFile(string $cacheFile): bool
    {
        return file_exists($cacheFile) && (filemtime($cacheFile) + $this->cacheLifetime) > time();
    }

    protected function getCacheFileName(string $path): string
    {
        return $this->cachePath . DIRECTORY_SEPARATOR . md5($path) . '.cache';
    }
}
