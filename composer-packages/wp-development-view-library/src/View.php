<?php

namespace HCC\View;

use HCC\View\Interfaces\TemplateEngineInterface;

class View
{
    protected string $path;
    protected array $data;
    protected TemplateEngineInterface $engine;

    public function __construct(string $path, array $data = [])
    {
        $this->path = $path;
        $this->data = $data;
    }

    public function setEngine(TemplateEngineInterface $engine): void
    {
        $this->engine = $engine;
    }

    public function render(): string
    {
        return $this->engine->render($this->path, $this->data);
    }
}
