<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests;

use InitPHP\Views\Facade\View;
use InitPHP\Views\Tests\Fixtures\RecordingAdapter;
use PHPUnit\Framework\TestCase;

use function view;

final class ViewHelperTest extends TestCase
{
    private RecordingAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new RecordingAdapter();
        View::via($this->adapter);
    }

    public function testRendersASingleViewName(): void
    {
        $output = view('dashboard', ['user' => 'admin']);

        self::assertSame('dashboard', $output);
        self::assertSame(
            [['views' => ['dashboard'], 'data' => ['user' => 'admin']]],
            $this->adapter->renders
        );
    }

    public function testRendersAListOfViewNames(): void
    {
        $output = view(['header', 'footer']);

        self::assertSame('header,footer', $output);
        self::assertSame(
            [['views' => ['header', 'footer'], 'data' => []]],
            $this->adapter->renders
        );
    }
}
