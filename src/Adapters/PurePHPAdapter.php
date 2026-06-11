<?php

/**
 * PurePHPAdapter.php
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

use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;
use Throwable;

use function extract;
use function is_dir;
use function is_file;
use function ltrim;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function realpath;
use function str_ends_with;
use function str_starts_with;

use const DIRECTORY_SEPARATOR;
use const EXTR_SKIP;

/**
 * Renders plain PHP view files.
 *
 * View files are ordinary `.php` files included at render time. Each file is
 * evaluated in an isolated scope that receives the merged data as local
 * variables and has no access to the adapter instance. Resolved paths are
 * confined to the configured base directory.
 */
class PurePHPAdapter extends AdapterAbstract
{
    /** Canonical base directory, always ending with a directory separator. */
    private string $dir;

    /**
     * @param string $viewDir Directory that contains the `.php` view files.
     * @throws ViewInvalidArgumentException If the directory does not exist.
     */
    public function __construct(string $viewDir)
    {
        $real = realpath($viewDir);
        if ($real === false || !is_dir($real)) {
            throw new ViewInvalidArgumentException('The "' . $viewDir . '" directory could not be found.');
        }

        $this->dir = $real . DIRECTORY_SEPARATOR;
    }

    /**
     * Render the queued view files and return their combined output.
     *
     * The queue and data are cleared once rendering finishes, whether or not
     * it succeeds, so the adapter can be reused safely.
     *
     * @return string
     * @throws ViewException If a queued view file cannot be found, or resolves
     *                       outside the configured view directory.
     */
    public function render(): string
    {
        $paths = [];
        foreach ($this->views as $view) {
            $path = $this->getPath($view);
            if (!is_file($path)) {
                throw new ViewException('"' . $path . '" view file not found.');
            }
            $real = realpath($path);
            if ($real === false || !str_starts_with($real, $this->dir)) {
                throw new ViewException('The "' . $view . '" view resolves outside the view directory.');
            }
            $paths[] = $real;
        }
        $data = $this->data;

        try {
            return $this->requireToString($paths, $data);
        } finally {
            $this->flush();
        }
    }

    /**
     * Include the given view files and capture their combined output.
     *
     * @param list<string>         $paths Absolute, verified view file paths.
     * @param array<string, mixed> $data  Variables exposed to each view.
     * @return string
     * @throws Throwable Re-thrown from a view file after the output buffer is discarded.
     */
    private function requireToString(array $paths, array $data): string
    {
        $renderer = static function (string $viewPath, array $viewData): void {
            extract($viewData, EXTR_SKIP);
            require $viewPath;
        };

        ob_start();
        try {
            foreach ($paths as $path) {
                $renderer($path, $data);
            }
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return (string) ob_get_clean();
    }

    /**
     * Resolve a view name to an absolute path, appending the `.php` extension
     * when it is missing.
     */
    private function getPath(string $view): string
    {
        if (!str_ends_with($view, '.php')) {
            $view .= '.php';
        }

        return $this->dir . ltrim($view, '/\\');
    }
}
