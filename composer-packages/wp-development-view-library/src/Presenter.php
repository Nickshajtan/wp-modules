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
    private TemplateResolverInterface $resolver;
    private TemplateLocatorInterface $locator;

    public function __construct(TemplateLocator $locator, TemplateResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        $this->locator = $locator;
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
        $view->setEngine($this->resolver->resolve(template: $template, path: $path));
        $this->clean();

        return $view;
    }

    protected function clean(): void
    {
        $this->data = [];
    }
}