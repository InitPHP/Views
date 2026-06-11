<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests;

use InitPHP\Views\Adapters\BladeConfig;
use PHPUnit\Framework\TestCase;

final class BladeConfigTest extends TestCase
{
    public function testGetReturnsStoredValue(): void
    {
        $config = new BladeConfig(['view.cache' => true]);

        self::assertTrue($config->get('view.cache'));
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        $config = new BladeConfig([]);

        self::assertSame('php', $config->get('view.compiled_extension', 'php'));
        self::assertNull($config->get('view.unknown'));
    }

    public function testArrayAccessReads(): void
    {
        $config = new BladeConfig(['view.paths' => ['/tmp/views']]);

        self::assertTrue(isset($config['view.paths']));
        self::assertFalse(isset($config['view.compiled']));
        self::assertSame(['/tmp/views'], $config['view.paths']);
        self::assertNull($config['view.compiled']);
    }

    public function testArrayAccessWrites(): void
    {
        $config = new BladeConfig([]);

        $config['view.cache'] = false;
        self::assertFalse($config['view.cache']);

        unset($config['view.cache']);
        self::assertFalse(isset($config['view.cache']));
    }
}
