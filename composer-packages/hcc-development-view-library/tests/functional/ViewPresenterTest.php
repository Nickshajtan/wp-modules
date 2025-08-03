<?php

use HCC\View\Cache\TemplateFileCache as Cache;
use HCC\View\Cache\TwigCacheAdapter;
use HCC\View\View;
use HCC\View\Presenter;
use HCC\View\TemplateLocator as Locator;
use HCC\View\TemplateEngineResolver as Resolver;
use HCC\View\Engine\PhpEngine;
use HCC\View\Engine\TwigEngine;
use HCC\View\Engine\BladeEngine;
use PHPUnit\Framework\TestCase;

class ViewPresenterTest extends TestCase
{
    private string $cacheDir;
    public function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/test_cache';
    }

    public function tearDown(): void
    {
        @rmdir($this->cacheDir);
    }

    public function testPhpViewOutput(): void
    {
        $testString = 'Hello world from PHP template';
        $templatePath = __DIR__ . '/_fixtures/simple.php';
        $view = new View( $templatePath, ['test' => $testString] );
        $view->setEngine(new PhpEngine(new Cache($this->cacheDir)));
        $this->assertSame("<p>$testString</p>", trim($view->render()));
        $this->assertSame("<p>$testString</p>", trim($view->render())); // To check cache

    }

    public function testPhpPresenter(): void
    {
        $testString = 'Hello world from PHP presenter';
        $presenter = new Presenter(
            new Locator('php-presenter', [__DIR__ . '/_fixtures/']),
            new Resolver('php-presenter', new Cache($this->cacheDir))
        );
        $view = $presenter->with('test', $testString)->view('simple.php');
        $this->assertSame("<p>$testString</p>", trim($view->render()));
        $this->assertSame("<p>$testString</p>", trim($view->render())); // To check cache
    }

    public function testTwigViewOutput(): void
    {
        if (!class_exists('Twig\Environment')) {
            $this->markTestSkipped('Twig is not installed');
        }

        $testString = 'Hello world from Twig template';
        $templatePath = __DIR__ . '/_fixtures/simple.twig';
        $view = new View( $templatePath, ['test' => $testString] );
        $view->setEngine(new TwigEngine(dirname($templatePath), new TwigCacheAdapter(new Cache($this->cacheDir))));
        $this->assertSame("<p>$testString</p>", trim($view->render()));
    }

    public function testTwigPresenter(): void
    {
        if (!class_exists('Twig\Environment')) {
            $this->markTestSkipped('Twig is not installed');
        }

        $testString = 'Hello world from Twig presenter';
        $presenter = new Presenter(
            new Locator('twig-presenter', [__DIR__ . '/_fixtures/']),
            new Resolver('twig-presenter', new Cache($this->cacheDir))
        );
        $view = $presenter->with('test', $testString)->view('simple.twig');
        $this->assertSame("<p>$testString</p>", trim($view->render()));
    }

    public function testBladeViewOutput(): void
    {
        $bladeClasses = [
            '\Illuminate\View\Engines\EngineResolver',
            '\Illuminate\Filesystem\Filesystem',
            '\Illuminate\View\Compilers\BladeCompiler',
            '\Illuminate\View\FileViewFinder',
            '\Illuminate\View\Engines\CompilerEngine',
            '\Illuminate\Events\Dispatcher',
            '\Illuminate\View\Factory'
        ];

        $anyMissing = array_filter($bladeClasses, fn($class) => class_exists($class));
        if (count($anyMissing) !== count($bladeClasses)) {
            $this->markTestSkipped('Blade is not installed');
        }

        $testString = 'Hello world from Blade template';
        $templatePath = __DIR__ . '/_fixtures/simple.blade.php';
        $view = new View( basename($templatePath), ['test' => $testString] );
        $view->setEngine(new BladeEngine(dirname($templatePath), new Cache($this->cacheDir)));
        $this->assertSame("<p>$testString</p>", trim($view->render()));
    }

    public function testBladePresenter(): void
    {
        $bladeClasses = [
            '\Illuminate\View\Engines\EngineResolver',
            '\Illuminate\Filesystem\Filesystem',
            '\Illuminate\View\Compilers\BladeCompiler',
            '\Illuminate\View\FileViewFinder',
            '\Illuminate\View\Engines\CompilerEngine',
            '\Illuminate\Events\Dispatcher',
            '\Illuminate\View\Factory'
        ];

        $anyMissing = array_filter($bladeClasses, fn($class) => class_exists($class));
        if (count($anyMissing) !== count($bladeClasses)) {
            $this->markTestSkipped('Blade is not installed');
        }

        $testString = 'Hello world from Blade presenter';
        $presenter = new Presenter(
            new Locator('blade-presenter', [__DIR__ . '/_fixtures/']),
            new Resolver('blade-presenter', new Cache($this->cacheDir))
        );
        $view = $presenter->with('test', $testString)->view('simple.blade.php');
        $this->assertSame("<p>$testString</p>", trim($view->render()));
    }
}
