<?php

/**
 * ViewExceptionInterface.php
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

use Throwable;

/**
 * Marker interface implemented by every exception this package throws.
 *
 * Catching this interface lets callers handle any failure originating from
 * InitPHP Views without depending on a concrete exception class.
 */
interface ViewExceptionInterface extends Throwable
{
}
