<?php

namespace HCC\View;

use HCC\View\Interfaces\TemplateLocatorInterface;
use HCC\View\Storage\StorageFacade;

class TemplateLocator implements TemplateLocatorInterface
{
    private StorageFacade $storage;
    private array $searchPaths;

    public function __construct(string $group, array $defaultPaths = [])
    {
        $this->storage = StorageFacade::for($group);
        $this->searchPaths = $defaultPaths;
    }

    public function locate(string $template): ?string
    {
        return $this->storage->remember("template.$template", function () use ($template) {
            foreach ($this->findTemplate($template) as $fullPath) {
                return $fullPath;
            }

            return null;
        });
    }

    private function findTemplate(string $template): \Generator {
        foreach ($this->searchPaths as $path) {
            $fullPath = $path . DIRECTORY_SEPARATOR . ltrim($template, DIRECTORY_SEPARATOR);
            if (file_exists($fullPath)) {
                yield $fullPath;
            }
        }
    }
}
