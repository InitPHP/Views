<?php

/**
 * BladeConfig.php
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

use ArrayAccess;

/**
 * Minimal configuration repository for the standalone Blade container.
 *
 * Illuminate's {@see \Illuminate\View\ViewServiceProvider} reads the view
 * configuration both as an array (`$config['view.paths']`) and through
 * `get()` (`$config->get('view.cache', true)`). A plain array only satisfies
 * the first form, so this class provides both without pulling in
 * `illuminate/config`.
 *
 * @internal Used by {@see BladeAdapter}; not part of the public API.
 * @implements ArrayAccess<string, mixed>
 */
class BladeConfig implements ArrayAccess
{
    /**
     * @param array<string, mixed> $items
     */
    public function __construct(private array $items)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }
}
