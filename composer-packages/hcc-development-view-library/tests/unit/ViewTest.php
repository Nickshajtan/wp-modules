<?php

use PHPUnit\Framework\TestCase;
use HCC\View\View;
use HCC\View\Interfaces\TemplateEngineInterface;

class ViewTest extends TestCase
{
    public function testRenderDelegatesToEngine(): void
    {
        $path = 'home.php';
        $data = ['title' => 'Hello'];

        $engineMock = $this->createMock(TemplateEngineInterface::class);
        $engineMock->expects($this->once())
            ->method('render')
            ->with($path, $data)
            ->willReturn('Rendered content');

        $view = new View($path, $data);
        $view->setEngine($engineMock);

        $this->assertEquals('Rendered content', $view->render());
    }
}
