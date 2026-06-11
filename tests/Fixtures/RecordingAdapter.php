<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests\Fixtures;

use InitPHP\Views\Adapters\AdapterAbstract;

use function implode;

/**
 * Adapter that records the views and data seen on each render. It has a
 * no-argument constructor, so it doubles as the subject for the
 * {@see \InitPHP\Views\Facade\View::via()} class-name code path.
 */
final class RecordingAdapter extends AdapterAbstract
{
    /** @var list<array{views: list<string>, data: array<string, mixed>}> */
    public array $renders = [];

    public function render(): string
    {
        $this->renders[] = [
            'views' => $this->views,
            'data' => $this->data,
        ];
        $output = implode(',', $this->views);
        $this->flush();

        return $output;
    }
}
