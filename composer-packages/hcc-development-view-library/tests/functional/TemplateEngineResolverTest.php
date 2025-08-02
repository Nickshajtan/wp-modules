<?php

use HCC\View\Cache\TemplateFileCache;
use HCC\View\Engine\PhpEngine;
use HCC\View\Engine\TwigEngine;
use HCC\View\Engine\BladeEngine;
use HCC\View\TemplateEngineResolver;
use PHPUnit\Framework\TestCase;

class TemplateEngineResolverTest extends TestCase
{
    private TemplateEngineResolver $resolver;
    private string $cacheDir;

    public function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/test_cache';
        $this->resolver = new TemplateEngineResolver(
            'test',
            $this->getMockBuilder(TemplateFileCache::class)
                ->setConstructorArgs([$this->cacheDir, 3600])
                ->onlyMethods(['set', 'get', 'delete', 'clear', 'purgeExpired'])
                ->getMock()
        );
    }

    public function tearDown(): void
    {
        @rmdir($this->cacheDir);
    }

    public function testResolvePhpEngine(): void
    {
        $this->assertInstanceOf(
            PhpEngine::class,
            $this->resolver->resolve('simple.php', __DIR__ . '/_fixtures/simple.php')
        );
    }

    public function testResolveTwigEngineWhenTwigAvailable(): void
    {
        if (!class_exists('Twig\Environment')) {
            $this->markTestSkipped('Twig is not installed');
        }

        $this->assertInstanceOf(
            TwigEngine::class,
            $this->resolver->resolve('simple.twig', __DIR__ . '/_fixtures/simple.twig')
        );
    }

    /*public function testResolveTwigThrowsIfTwigMissing(): void
    {
        if (class_exists('Twig\Environment')) {
            $this->markTestSkipped('Twig is installed — cannot test missing Twig.');
        }

        $cache = $this->createMock(TemplateCacheInterface::class);
        $resolver = new TemplateEngineResolver('group', $cache);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Twig is not installed');

        $resolver->resolve('test.twig', '/path/test.twig');
    }*/

    public function testResolveBladeEngineWhenBladeAvailable(): void
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

        $this->assertInstanceOf(
            BladeEngine::class,
            $this->resolver->resolve('simple.blade.php', __DIR__ . '/_fixtures/simple.blade.php')
        );
    }

    /*
    public function testResolveBladeThrowsIfBladeMissing(): void
    {
        $bladeClasses = [
            '\Illuminate\Filesystem\Filesystem',
            '\Illuminate\View\Compilers\BladeCompiler',
            '\Illuminate\View\FileViewFinder',
            '\Illuminate\View\Engines\CompilerEngine',
            '\Illuminate\View\Factory'
        ];

        $anyMissing = array_filter($bladeClasses, fn($class) => !class_exists($class));
        if (empty($anyMissing)) {
            $this->markTestSkipped('Blade is installed — cannot test missing Blade.');
        }

        $cache = $this->createMock(TemplateCacheInterface::class);
        $resolver = new TemplateEngineResolver('group', $cache);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Blade is not installed');

        $resolver->resolve('template.blade.php', '/path/template.blade.php');
    }*/
}
