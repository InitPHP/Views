<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests\Facade;

use InitPHP\Views\Adapters\PurePHPAdapter;
use InitPHP\Views\Exceptions\ViewAdapterException;
use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Facade\View;
use InitPHP\Views\Tests\Fixtures\RecordingAdapter;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ViewTest extends TestCase
{
    public function testViaAcceptsAnInstanceAndForwardsCalls(): void
    {
        $adapter = new RecordingAdapter();
        View::via($adapter);

        View::setView('home')->setData(['user' => 'admin']);
        $output = View::render();

        self::assertSame('home', $output);
        self::assertSame(
            [['views' => ['home'], 'data' => ['user' => 'admin']]],
            $adapter->renders
        );
    }

    public function testCallStaticForwardsGetData(): void
    {
        View::via(new RecordingAdapter());
        View::setData(['key' => 'value']);

        self::assertSame('value', View::getData('key'));
    }

    public function testViaAcceptsANoArgumentAdapterClassName(): void
    {
        View::via(RecordingAdapter::class);

        View::setView('a', 'b');

        self::assertSame('a,b', View::render());
    }

    public function testViaThrowsForUnknownClass(): void
    {
        $this->expectException(ViewAdapterException::class);

        View::via('InitPHP\\Views\\Adapters\\DoesNotExist');
    }

    public function testViaThrowsForClassNotImplementingTheInterface(): void
    {
        $this->expectException(ViewAdapterException::class);

        View::via(stdClass::class);
    }

    public function testViaThrowsWhenAdapterNeedsConstructorArguments(): void
    {
        $this->expectException(ViewAdapterException::class);

        View::via(PurePHPAdapter::class);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCallingBeforeRegistrationThrows(): void
    {
        $this->expectException(ViewException::class);

        View::render();
    }
}
