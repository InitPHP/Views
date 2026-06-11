<?php

/**
 * AdapterAbstract.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    https://github.com/InitPHP/Views/blob/main/LICENSE  MIT
 * @link       https://github.com/InitPHP/Views
 */

declare(strict_types=1);

namespace InitPHP\Views\Adapters;

use InitPHP\Views\Interfaces\ViewAdapterInterface;

use function get_object_vars;

/**
 * Shared state and behaviour for view adapters.
 *
 * Concrete adapters only have to implement {@see self::render()}; queueing
 * views, merging data and reading it back are handled here.
 */
abstract class AdapterAbstract implements ViewAdapterInterface
{
    /** @var list<string> Queued view identifiers, in render order. */
    protected array $views = [];

    /** @var array<string, mixed> Data merged across setData() calls. */
    protected array $data = [];

    /**
     * Render the queued views; equivalent to calling {@see self::render()}.
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @inheritDoc
     */
    public function setView(string ...$views): static
    {
        foreach ($views as $view) {
            $this->views[] = $view;
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @param array<string, mixed>|object $data
     */
    public function setData(array|object $data): static
    {
        if (\is_object($data)) {
            $data = get_object_vars($data);
        }
        foreach ($data as $key => $value) {
            $this->data[(string) $key] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getData(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return \array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    /**
     * @inheritDoc
     */
    abstract public function render(): string;

    /**
     * Clear the queued views and merged data.
     *
     * Adapters call this once a render finishes so the instance can be reused
     * for an independent render without leaking the previous run's state.
     */
    protected function flush(): void
    {
        $this->views = [];
        $this->data = [];
    }
}
