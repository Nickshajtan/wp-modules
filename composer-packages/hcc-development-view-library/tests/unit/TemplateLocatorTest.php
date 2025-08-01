<?php

use PHPUnit\Framework\TestCase;
use HCC\View\TemplateLocator;
use HCC\View\Storage\StorageFacade;
class TemplateLocatorTest extends TestCase
{
    private static string $tempDir;

    public static function setUpBeforeClass(): void
    {
        static::$tempDir = sys_get_temp_dir() . '/tpl_test_' . uniqid();
        mkdir(static::$tempDir, 0777, true);
    }

    public static function tearDownAfterClass(): void
    {
        array_map('unlink', glob(static::$tempDir . "/*"));
        rmdir(static::$tempDir);
    }

    public function testLocatesTemplateIfFileExists(): void
    {
        $filename = 'test-template.php';
        $fullPath = static::$tempDir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($fullPath, '<?= "Hello" ?>');

        $locator = new TemplateLocator('test-group', [static::$tempDir]);
        $result = $locator->locate($filename);
        $this->assertSame($fullPath, $result);
    }

    public function testReturnsNullIfTemplateNotFound(): void
    {
        $locator = new TemplateLocator('test-group', [static::$tempDir]);
        $result = $locator->locate('non-existent.php');
        $this->assertNull($result);
    }

    public function testUsesStorageCache(): void
    {
        $locator = new class(static::$tempDir) extends TemplateLocator {
            public function __construct(string $tmpDir) {
                parent::__construct('test-group', [$tmpDir]);
                $this->storage = new class('test-group') extends StorageFacade {
                    private $rememberCallback;
                    public function __construct(string $group)
                    {
                        parent::__construct($group);
                    }

                    public function remember(string $key, callable $callback): mixed
                    {
                        if ($this->rememberCallback) {
                            return call_user_func($this->rememberCallback, $key, $callback);
                        }

                        return $callback();
                    }

                    public function setRememberCallback(callable $callback): void
                    {
                        $this->rememberCallback = $callback;
                    }
                };
            }

            public function getStorage(): StorageFacade
            {
                return $this->storage;
            }
        };
        $storage = $locator->getStorage();
        $this->assertTrue( is_a($storage, StorageFacade::class) );
        $storage->setRememberCallback(fn() => 'cached-path.php');
        $result = $locator->locate('any-template.php');
        $this->assertSame('cached-path.php', $result);
    }
}
