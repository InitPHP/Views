<?php
/**
 * View_helper.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

if (!function_exists('view')) {
    function view(string|array $views, array|object $data = []): string
    {
        if (is_string($views)) {
            $views = [$views];
        }
        return \InitPHP\Views\Facade\View::setView(...$views)
            ->setData($data)
            ->render();
    }
}
