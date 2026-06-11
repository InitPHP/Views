<?php

/**
 * BladeContainer.php
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

use Illuminate\Container\Container;
use Throwable;

/**
 * Container shim for running Blade outside a full Laravel application.
 *
 * Illuminate's {@see \Illuminate\View\ViewServiceProvider} registers shutdown
 * hooks through `terminating()`, a method that exists on the framework
 * Application but not on the base {@see Container}. This subclass adds it so
 * Blade can be bootstrapped with a plain container; the queued callbacks run
 * when the container is destroyed.
 *
 * @internal Used by {@see BladeAdapter}; not part of the public API.
 */
class BladeContainer extends Container
{
    /** @var list<callable> */
    private array $terminatingCallbacks = [];

    public function terminating(callable $callback): self
    {
        $this->terminatingCallbacks[] = $callback;

        return $this;
    }

    public function __destruct()
    {
        foreach ($this->terminatingCallbacks as $callback) {
            try {
                $callback();
            } catch (Throwable) {
                // Shutdown hooks must not surface errors while tearing down.
            }
        }
    }
}
