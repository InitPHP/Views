<?php

/**
 * View.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    https://github.com/InitPHP/Views/blob/main/LICENSE  MIT
 * @link       https://github.com/InitPHP/Views
 */

declare(strict_types=1);

namespace InitPHP\Views\Facade;

use ArgumentCountError;
use InitPHP\Views\Exceptions\ViewAdapterException;
use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Interfaces\ViewAdapterInterface;

use function class_exists;
use function is_a;

/**
 * Static entry point to the active view adapter.
 *
 * Register an adapter once with {@see self::via()}; every other call is
 * forwarded statically to that adapter instance.
 *
 * @mixin ViewAdapterInterface
 * @method static ViewAdapterInterface setView(string ...$views)
 * @method static ViewAdapterInterface setData(array<string, mixed>|object $data)
 * @method static mixed                getData(?string $key = null, mixed $default = null)
 * @method static string               render()
 */
final class View
{
    /** The adapter all calls are forwarded to once registered. */
    private static ViewAdapterInterface $viewInstance;

    /**
     * The facade is purely static and must not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Forward a static call to the registered adapter.
     *
     * @param string            $name
     * @param array<int, mixed> $arguments
     * @return mixed
     * @throws ViewException If no adapter has been registered yet.
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return self::getInstance()->{$name}(...$arguments);
    }

    /**
     * Register the adapter that backs the facade.
     *
     * Accepts either a ready-to-use adapter instance or the class name of an
     * adapter that can be constructed without arguments.
     *
     * @param string|ViewAdapterInterface $adapter Adapter instance, or the class name of a no-argument adapter.
     * @throws ViewAdapterException If the class is missing, does not implement
     *                              {@see ViewAdapterInterface}, or requires constructor arguments.
     */
    public static function via(string|ViewAdapterInterface $adapter): void
    {
        if ($adapter instanceof ViewAdapterInterface) {
            self::$viewInstance = $adapter;

            return;
        }

        if (!class_exists($adapter)) {
            throw new ViewAdapterException('The adapter class "' . $adapter . '" does not exist.');
        }
        if (!is_a($adapter, ViewAdapterInterface::class, true)) {
            throw new ViewAdapterException('"' . $adapter . '" must implement ' . ViewAdapterInterface::class . '.');
        }

        try {
            self::$viewInstance = new $adapter();
        } catch (ArgumentCountError $e) {
            throw new ViewAdapterException(
                'The adapter "' . $adapter . '" cannot be created without constructor arguments; '
                . 'pass a configured instance to View::via() instead.',
                0,
                $e
            );
        }
    }

    /**
     * Return the registered adapter.
     *
     * @return ViewAdapterInterface
     * @throws ViewException If no adapter has been registered yet.
     */
    protected static function getInstance(): ViewAdapterInterface
    {
        if (!isset(self::$viewInstance)) {
            throw new ViewException('No view adapter has been registered. Call View::via() first.');
        }

        return self::$viewInstance;
    }
}
