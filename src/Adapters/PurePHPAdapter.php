<?php
/**
 * PurePHPAdapter.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

namespace InitPHP\Views\Adapters;

use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;

use const PHP_VERSION_ID;
use const DIRECTORY_SEPARATOR;
use function is_dir;
use function is_file;
use function rtrim;
use function ltrim;
use function extract;
use function ob_start;
use function ob_end_clean;
use function substr;

class PurePHPAdapter extends AdapterAbstract implements \InitPHP\Views\Interfaces\ViewAdapterInterface
{

    private string $dir;

    private string $content = '';

    public function __construct(string $viewDir)
    {
        if (!is_dir($viewDir)) {
            throw new ViewInvalidArgumentException('The "' . $viewDir . '" directory could not be found.');
        }

        $this->dir = rtrim($viewDir, '\\/') . DIRECTORY_SEPARATOR;
    }

    public function __destruct()
    {
        unset($this->dir, $this->content);
    }

    public function render(): string
    {
        $views = [];
        foreach ($this->views as $view) {
            $path = $this->get_path($view);
            if(!is_file($path)){
                throw new ViewException('"' . $path . '" view file not found.');
            }
            $views[] = $path;
        }
        $this->views = [];

        if(!empty($this->data)){
            $data = $this->data;
            extract($data);
        }

        ob_start(function ($tmp) {
            $this->content .= $tmp;
        });
        foreach ($views as $view) {
            require $view;
        }
        unset($views);
        ob_end_clean();
        $this->data = [];

        return $this->content;
    }

    private function get_path(string $view): string
    {
        if (PHP_VERSION_ID > 80000) {
            !\str_ends_with($view, '.php') && $view .= '.php';
        } else {
            !substr($view, -4) != '.php' && $view .= '.php';
        }

        return $this->dir . ltrim($view, '/\\');
    }

}
