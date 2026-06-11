<?php

/**
 * ViewInvalidArgumentException.php
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

use InvalidArgumentException;

/**
 * Thrown when a method receives an argument of an invalid value, for example a
 * view or cache directory that does not exist.
 */
class ViewInvalidArgumentException extends InvalidArgumentException implements ViewExceptionInterface
{
}
