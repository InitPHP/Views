<?php
/**
 * View.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

namespace InitPHP\Views\Facade;

use InitPHP\Views\Exceptions\ViewAdapterException;
use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Interfaces\ViewAdapterInterface;

use function is_string;
use function is_object;
use function class_exists;

/**
 * @mixin ViewAdapterInterface
 * @method static ViewAdapterInterface setView(string ...$views)
 * @method static ViewAdapterInterface setData(array|object $data)
 * @method static ViewAdapterInterface getData(?string $key, mixed $default = null)
 * @method static string render()
 */
class View
{

    private static ViewAdapterInterface $viewInstance;


    public function __toString(): string
    {
        return self::getInstance()->__toString();
    }

    public function __call(string $name, array $arguments)
    {
        return self::getInstance()->{$name}(...$arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return self::getInstance()->{$name}(...$arguments);
    }

    public static function via(string|ViewAdapterInterface $adapter): void
    {
        if (is_string($adapter) && !class_exists($adapter)) {
            throw new ViewAdapterException();
        }

        $adapterObj = !is_object($adapter) ? new $adapter() : $adapter;

        if(!($adapterObj instanceof ViewAdapterInterface)){
            throw new ViewAdapterException();
        }
        self::$viewInstance = $adapterObj;
    }

    protected static function getInstance(): ViewAdapterInterface
    {
        if (!isset(self::$viewInstance)) {
            throw new ViewException();
        }

        return self::$viewInstance;
    }

}
