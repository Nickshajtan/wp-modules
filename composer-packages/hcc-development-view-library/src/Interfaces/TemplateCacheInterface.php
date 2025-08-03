<?php

namespace HCC\View\Interfaces;

interface TemplateCacheInterface
{
    public function set(string $filename, string $content, array $context = []): void;
    public function get(string $filename, array $context = []): ?string;
    public function delete(string $filename, array $context = []): void;
    public function clear(): void;
    public function getCacheDirectory(): string;

    public function purgeExpired(): void;
}
