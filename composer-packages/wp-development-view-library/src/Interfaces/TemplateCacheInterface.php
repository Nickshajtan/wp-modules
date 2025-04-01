<?php

namespace HCC\View\Interfaces;

interface TemplateCacheInterface
{
    public function set(string $filename, string $content): void;
    public function get(string $filename): ?string;
    public function delete(string $filename): void;
    public function clear(): void;
}
