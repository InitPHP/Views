<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests;

use InitPHP\Views\Adapters\TwigAdapter;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;
use InitPHP\Views\Tests\Concerns\TempViewDirectory;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

use function class_exists;

final class TwigAdapterTest extends TestCase
{
    use TempViewDirectory;

    private string $viewDir;

    private string $cacheDir;

    protected function setUp(): void
    {
        if (!class_exists(Environment::class)) {
            self::markTestSkipped('twig/twig is not installed.');
        }
        $this->viewDir = $this->makeTempDir();
        $this->cacheDir = $this->makeTempDir();
    }

    private function adapter(): TwigAdapter
    {
        return new TwigAdapter($this->viewDir, $this->cacheDir);
    }

    public function testConstructorRejectsMissingViewDirectory(): void
    {
        $this->expectException(ViewInvalidArgumentException::class);

        new TwigAdapter($this->viewDir . '/missing', $this->cacheDir);
    }

    public function testConstructorRejectsMissingCacheDirectory(): void
    {
        $this->expectException(ViewInvalidArgumentException::class);

        new TwigAdapter($this->viewDir, $this->cacheDir . '/missing');
    }

    public function testRendersTemplateWithData(): void
    {
        $this->writeView($this->viewDir, 'hello.twig', 'Hello {{ name }}!');

        $output = $this->adapter()->setView('hello.twig')->setData(['name' => 'admin'])->render();

        self::assertSame('Hello admin!', $output);
    }

    public function testRendersMultipleTemplatesInOrder(): void
    {
        $this->writeView($this->viewDir, 'a.twig', 'A');
        $this->writeView($this->viewDir, 'b.twig', 'B');

        $output = $this->adapter()->setView('a.twig', 'b.twig')->render();

        self::assertSame('AB', $output);
    }

    public function testStateIsResetBetweenRenders(): void
    {
        $this->writeView($this->viewDir, 'hi.twig', 'Hi {{ name }}');
        $adapter = $this->adapter();

        $first = $adapter->setView('hi.twig')->setData(['name' => 'a'])->render();
        $second = $adapter->setView('hi.twig')->setData(['name' => 'b'])->render();

        self::assertSame('Hi a', $first);
        self::assertSame('Hi b', $second);
    }

    public function testGetEnvironmentExposesTwig(): void
    {
        self::assertInstanceOf(Environment::class, $this->adapter()->getEnvironment());
    }
}
