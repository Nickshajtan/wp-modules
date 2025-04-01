<?php

namespace HCC\View;

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
        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }
    }

    protected function getFilePath(string $filename): string
    {
        $hash = md5($filename);
        $subDir = $this->cacheDir . $hash[0] . DIRECTORY_SEPARATOR . $hash[1] . DIRECTORY_SEPARATOR;
        $this->createDir($subDir);

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $cacheExt = $extension ? '.' . $extension . '.' . static::CACHE_EXTENSION : '.' . static::CACHE_EXTENSION;

        return $subDir . $hash . $cacheExt;
    }

    public function getCacheDirectory(): string
    {
        return $this->cacheDir;
    }

    public function set(string $filename, string $content): void
    {
        $filePath = $this->getFilePath($filename);
        $fp = fopen($filePath, 'c');
        if ($fp) {
            flock($fp, LOCK_EX);
            ftruncate($fp, 0);
            fwrite($fp, $content);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            touch($filePath);
        }
    }

    public function get(string $filename): ?string
    {
        $filePath = $this->getFilePath($filename);
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
        $content = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $content;
    }

    public function delete(string $filename): void
    {
        $filePath = $this->getFilePath($filename);
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
