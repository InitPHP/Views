<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests\Fixtures;

use InitPHP\Views\Adapters\AdapterAbstract;

/**
 * Minimal concrete adapter that exposes {@see AdapterAbstract}'s protected
 * state so the shared behaviour can be asserted in isolation.
 */
final class StubAdapter extends AdapterAbstract
{
    public function render(): string
    {
        return '';
    }

    /**
     * @return list<string>
     */
    public function exposedViews(): array
    {
        return $this->views;
    }

    /**
     * @return array<string, mixed>
     */
    public function exposedData(): array
    {
        return $this->data;
    }

    public function exposedFlush(): void
    {
        $this->flush();
    }
}
