<?php

namespace HCC\View\Interfaces;

interface TemplateResolverInterface
{
    public function resolve(string $template, string $path, string $cachePath): TemplateEngineInterface;
}
