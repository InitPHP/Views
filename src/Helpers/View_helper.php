<?php

/**
 * View_helper.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    https://github.com/InitPHP/Views/blob/main/LICENSE  MIT
 * @link       https://github.com/InitPHP/Views
 */

declare(strict_types=1);

use InitPHP\Views\Facade\View;

if (!function_exists('view')) {
    /**
     * Render one or more views through the registered View adapter.
     *
     * @param string|array<int, string>  $views One view name or a list of names, rendered in order.
     * @param array<string, mixed>|object $data Data exposed to the views.
     * @return string The concatenated render output.
     * @throws \InitPHP\Views\Exceptions\ViewException If no adapter is registered or a view cannot be found.
     */
    function view(string|array $views, array|object $data = []): string
    {
        if (is_string($views)) {
            $views = [$views];
        }

        return View::setView(...$views)
            ->setData($data)
            ->render();
    }
}
