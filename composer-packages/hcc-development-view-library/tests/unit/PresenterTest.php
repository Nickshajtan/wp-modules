<?php

use PHPUnit\Framework\TestCase;
use HCC\View\Presenter;
use HCC\View\View;
use HCC\View\Interfaces\TemplateLocatorInterface;
use HCC\View\Interfaces\TemplateResolverInterface;
use HCC\View\Interfaces\TemplateEngineInterface;

class PresenterTest extends TestCase
{
    public function testViewReturnsViewInstanceWithResolvedPathAndEngine(): void
    {
        $template = 'home';
        $resolvedPath = DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'home.php';
        $defaultPath = '/fallback.php';
        $data = ['title' => 'Hello'];

        $locator = $this->createMock(TemplateLocatorInterface::class);
        $locator->expects($this->once())
            ->method('locate')
            ->with($template)
            ->willReturn($resolvedPath);

        $engine = $this->createMock(TemplateEngineInterface::class);
        $resolver = $this->createMock(TemplateResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with($template, $resolvedPath)
            ->willReturn($engine);

        $presenter = new Presenter($locator, $resolver);
        $presenter->with('title', 'Hello');

        $view = $presenter->view($template, $defaultPath);
        $this->assertInstanceOf(View::class, $view);
        $engine->expects($this->once())
            ->method('render')
            ->with($resolvedPath, $data)
            ->willReturn('rendered output');

        $this->assertEquals('rendered output', $view->render());
    }

    public function testViewFallsBackToDefaultPathIfLocatorReturnsNull(): void
    {
        $template = 'unknown';
        $defaultPath = '/default.php';

        $locator = $this->createMock(TemplateLocatorInterface::class);
        $locator->expects($this->once())
            ->method('locate')
            ->with($template)
            ->willReturn(null);

        $engine = $this->createMock(TemplateEngineInterface::class);

        $resolver = $this->createMock(TemplateResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with($template, $defaultPath)
            ->willReturn($engine);

        $presenter = new Presenter($locator, $resolver);

        $view = $presenter->view($template, $defaultPath);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testCleanClearsDataAfterView(): void
    {
        $locator = $this->createMock(TemplateLocatorInterface::class);
        $locator->method('locate')->willReturn('/path');

        $engine = $this->createMock(TemplateEngineInterface::class);

        $resolver = $this->createMock(TemplateResolverInterface::class);
        $resolver->method('resolve')->willReturn($engine);

        $presenter = new Presenter($locator, $resolver);
        $presenter->with('x', 1);
        $presenter->view('a');

        $presenter->with('y', 2);
        $view2 = $presenter->view('b');


        $reflection = new \ReflectionClass($view2);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);
        $data = $property->getValue($view2);

        $this->assertArrayNotHasKey('x', $data);
        $this->assertArrayHasKey('y', $data);
    }
}
