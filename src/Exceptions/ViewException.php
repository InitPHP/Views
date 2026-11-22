<?php

/**
 * ViewException.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com>
 * @copyright  Copyright © 2022 InitPHP
 * @license    https://github.com/InitPHP/Views/blob/main/LICENSE  MIT
 * @link       https://github.com/InitPHP/Views
 */

declare(strict_types=1);

namespace InitPHP\Views\Exceptions;

use RuntimeException;

/**
 * Thrown for runtime view failures, such as using the View facade before an
 * adapter has been registered or rendering a view file that cannot be found.
 */
class ViewException extends RuntimeException implements ViewExceptionInterface {}
