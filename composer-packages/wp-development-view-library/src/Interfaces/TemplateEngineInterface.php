<?php

namespace HCC\View\Interfaces;

interface TemplateEngineInterface
{
    public function render(string $path, array $data): string;
}
