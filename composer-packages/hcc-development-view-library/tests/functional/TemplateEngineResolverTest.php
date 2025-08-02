<?php

use HCC\View\Cache\TemplateFileCache;
use HCC\View\Engine\PhpEngine;
use HCC\View\Engine\TwigEngine;
use HCC\View\TemplateEngineResolver;
use PHPUnit\Framework\TestCase;

class TemplateEngineResolverTest extends TestCase
{
    private TemplateEngineResolver $resolver;

    /*public static function tearDownAfterClass(): void
    {
        $workingDir = __DIR__ . DIRECTORY_SEPARATOR . '_dependencies/vendor';
        if ( is_dir($workingDir) ) {
            @rmdir($workingDir);
        }
    }*/

    public function setUp(): void
    {
        $this->resolver = new TemplateEngineResolver(
            'test',
            $this->createMock(TemplateFileCache::class)
        );
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

    /*
     *
     * private function makeResolverWithInjectedStorage(StorageFacade $storage, TemplateCacheInterface $cache): TemplateEngineResolver
    {
        $resolver = new TemplateEngineResolver('group', $cache);
        $ref = new \ReflectionClass($resolver);
        $prop = $ref->getProperty('storage');
        $prop->setAccessible(true);
        $prop->setValue($resolver, $storage);

        return $resolver;
    }

    public function testResolveTwigThrowsIfTwigMissing(): void
    {
        if (class_exists('Twig\Environment')) {
            $this->markTestSkipped('Twig is installed — cannot test missing Twig.');
        }

        $cache = $this->createMock(TemplateCacheInterface::class);
        $resolver = new TemplateEngineResolver('group', $cache);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Twig is not installed');

        $resolver->resolve('test.twig', '/path/test.twig');
    }

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
