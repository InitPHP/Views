<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests;

use InitPHP\Views\Exceptions\ViewAdapterException;
use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Exceptions\ViewExceptionInterface;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExceptionTest extends TestCase
{
    public function testViewExceptionExtendsRuntimeAndImplementsMarker(): void
    {
        $exception = new ViewException('boom');

        self::assertInstanceOf(RuntimeException::class, $exception);
        self::assertInstanceOf(ViewExceptionInterface::class, $exception);
    }

    public function testAdapterExceptionExtendsViewException(): void
    {
        $exception = new ViewAdapterException('boom');

        self::assertInstanceOf(ViewException::class, $exception);
        self::assertInstanceOf(ViewExceptionInterface::class, $exception);
    }

    public function testInvalidArgumentExceptionImplementsMarker(): void
    {
        $exception = new ViewInvalidArgumentException('boom');

        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(ViewExceptionInterface::class, $exception);
    }

    public function testEveryPackageExceptionCanBeCaughtByTheMarkerInterface(): void
    {
        $exceptions = [
            new ViewException(),
            new ViewAdapterException(),
            new ViewInvalidArgumentException(),
        ];

        foreach ($exceptions as $exception) {
            self::assertInstanceOf(ViewExceptionInterface::class, $exception);
        }
    }
}
