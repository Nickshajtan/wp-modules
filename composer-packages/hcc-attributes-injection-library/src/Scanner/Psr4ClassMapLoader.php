<?php

namespace HCC\Attributes\Scanner;

class Psr4ClassMapLoader
{
    protected array $psr4;

    public function __construct(array $psr4List)
    {
        $this->psr4 = $psr4List;
    }

    public function getClassMap(): array
    {
        return iterator_to_array($this->getClassMapGenerator());
    }

    public function getClassMapGenerator(): \Generator
    {
        foreach ($this->psr4 as $namespace => $paths) {
            foreach ((array)$paths as $path) {
                yield from $this->scanDirectory($path, $namespace);
            }
        }
    }

    protected function scanDirectory(string $path, string $namespace): array
    {
        $map = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile() && 'php' === $file->getExtension()) {
                $fullPath = $file->getRealPath();
                $relativePath = str_replace([$path . DIRECTORY_SEPARATOR, '.php'], '', $fullPath);
                $relativeClass = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
                $class = $namespace . $relativeClass;
                $map[$class] = $fullPath;
            }
        }

        return $map;
    }
}