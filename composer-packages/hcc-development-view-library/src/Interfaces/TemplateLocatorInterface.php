<?php

namespace HCC\View\Interfaces;

interface TemplateLocatorInterface
{
    public function locate(string $template): ?string;
}
