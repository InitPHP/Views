<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests;

use Illuminate\Container\Container;
use InitPHP\Views\Adapters\BladeContainer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

use function class_exists;

final class BladeContainerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(Container::class)) {
            self::markTestSkipped('illuminate/container is not installed.');
        }
    }

    public function testTerminatingIsFluent(): void
    {
        $container = new BladeContainer();

        self::assertSame($container, $container->terminating(static fn () => null));
    }

    public function testTerminatingCallbacksRunOnDestruction(): void
    {
        $state = new stdClass();
        $state->ran = 0;

        $container = new BladeContainer();
        $container->terminating(static function () use ($state): void {
            $state->ran++;
        });

        unset($container);

        self::assertSame(1, $state->ran);
    }

    public function testDestructionSwallowsCallbackErrors(): void
    {
        $state = new stdClass();
        $state->ran = false;

        $container = new BladeContainer();
        $container->terminating(static function (): void {
            throw new RuntimeException('boom');
        });
        $container->terminating(static function () use ($state): void {
            $state->ran = true;
        });

        unset($container);

        self::assertTrue($state->ran, 'A throwing shutdown hook must not stop the others.');
    }
}
