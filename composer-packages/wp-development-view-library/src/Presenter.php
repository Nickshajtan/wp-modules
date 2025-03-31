<?php

namespace HCC\View;

use HCC\View\Interfaces\TemplateResolverInterface;
use HCC\View\Interfaces\TemplateLocatorInterface;

/**
 * Facade class for view rendering system
 */
class Presenter
{
    protected array $data = [];
    protected string $cachePath;
    private TemplateResolverInterface $resolver;
    private TemplateLocatorInterface $locator;

    public function __construct(TemplateLocator $locator, TemplateResolverInterface $resolver, string $cachePath)
    {
        $this->resolver = $resolver;
        $this->locator = $locator;
        $this->cachePath = $cachePath;
    }

    public function with(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function view(string $template, string $defaultPath = ''): View
    {
        $path = $this->locator->locate($template) ?? $defaultPath;
        $view = new View($path, $this->data);
        $view->setEngine($this->resolver->resolve(template: $template, path: $path, cachePath: $this->cachePath));
        $this->clean();

        return $view;
    }

    protected function clean(): void
    {
        $this->data = [];
    }
}