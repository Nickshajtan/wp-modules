<?php

namespace HCC\View\Cache;

use HCC\View\Interfaces\TemplateCacheInterface;
class TemplateFileCache implements TemplateCacheInterface
{
    protected string $cacheDir;

    protected int $ttl;

    protected const CACHE_EXTENSION = 'cache';

    public function __construct(string $cacheDir, int $ttl = 3600) {
        $this->cacheDir = str_ends_with($cacheDir, DIRECTORY_SEPARATOR) ? $cacheDir : $cacheDir . DIRECTORY_SEPARATOR;
        $this->ttl = $ttl;
        $this->createDir($cacheDir);
    }

    protected function createDir(string $dirname): void
    {
        if (!empty($dirname) && !is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }
    }

    protected function getFilePath(string $filename, array $context = []): string
    {
        $hash = $this->generateCacheKey($filename, $context);
        $subDir = $this->cacheDir . substr($hash, 0, 2) . DIRECTORY_SEPARATOR;
        $this->createDir($subDir);

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $cacheExt = $extension ? '.' . $extension . '.' . static::CACHE_EXTENSION : '.' . static::CACHE_EXTENSION;

        return $subDir . $hash . $cacheExt;
    }

    protected function generateCacheKey(string $key, array $context = []): string
    {
        $normalized = $this->normalizeContext($context);
        return $key . '-' . hash('sha256', serialize($normalized));
    }

    protected function normalizeContext(array $context): array
    {
        ksort($context);
        foreach ($context as &$value) {
            if (is_array($value)) {
                $value = $this->normalizeContext($value);
            } elseif (is_object($value)) {
                $value = method_exists($value, '__toString') ? (string) $value : get_class($value);
            }
        }

        return $context;
    }

    public function getCacheDirectory(): string
    {
        return $this->cacheDir;
    }

    public function set(string $filename, string $content, array $context = []): void
    {
        $filePath = $this->getFilePath($filename, $context);
        $fp = fopen($filePath, 'c');
        if ($fp) {
            $contentToWrite = preg_match('/\.(blade|twig|php)$/', $filename) ?
                "<?php exit; ?>\n" . $content : $content;

            flock($fp, LOCK_EX);
            ftruncate($fp, 0);
            fwrite($fp, $contentToWrite);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            touch($filePath);
        }
    }

    public function get(string $filename, array $context = []): ?string
    {
        $filePath = $this->getFilePath($filename, $context);
        if (!file_exists($filePath)) {
            return null;
        }

        if (time() - filemtime($filePath) > $this->ttl) {
            $this->delete($filename);
            return null;
        }

        $fp = fopen($filePath, 'r');
        if (!$fp) {
            return null;
        }

        flock($fp, LOCK_SH);
        if ( preg_match('/\.(blade|twig|php)$/', $filename) ) {
            fgets($fp);
        }

        $content = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $content;
    }

    public function delete(string $filename, array $context = []): void
    {
        $filePath = $this->getFilePath($filename, $context);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function clear(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            } elseif ($file->isDir()) {
                rmdir($file->getPathname());
            }
        }
    }

    public function purgeExpired(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && time() - $file->getMTime() > $this->ttl) {
                unlink($file->getPathname());
            }
        }
    }
}
