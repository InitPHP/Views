<?php

/**
 * ViewAdapterException.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    https://github.com/InitPHP/Views/blob/main/LICENSE  MIT
 * @link       https://github.com/InitPHP/Views
 */

declare(strict_types=1);

namespace InitPHP\Views\Exceptions;

/**
 * Thrown when the adapter given to {@see \InitPHP\Views\Facade\View::via()} is
 * not a usable {@see \InitPHP\Views\Interfaces\ViewAdapterInterface}
 * implementation.
 */
class ViewAdapterException extends ViewException
{
}
