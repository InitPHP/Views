<?php

/**
 * TwigAdapter.php
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

use InitPHP\Views\Exceptions\ViewInvalidArgumentException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function is_dir;

/**
 * Renders templates with the Symfony Twig engine.
 *
 * Twig requires the file extension to be part of the view name (for example
 * `page.html.twig`); unlike {@see PurePHPAdapter} no extension is added.
 */
class TwigAdapter extends AdapterAbstract
{
    private Environment $twig;

    /**
     * @param string $viewDir  Directory that holds the Twig templates.
     * @param string $cacheDir Writable directory for the compiled templates.
     * @throws ViewInvalidArgumentException If either directory does not exist.
     */
    public function __construct(string $viewDir, string $cacheDir)
    {
        if (!is_dir($viewDir)) {
            throw new ViewInvalidArgumentException('The view directory "' . $viewDir . '" could not be found.');
        }
        if (!is_dir($cacheDir)) {
            throw new ViewInvalidArgumentException('The cache directory "' . $cacheDir . '" could not be found.');
        }

        $this->twig = new Environment(new FilesystemLoader($viewDir), [
            'cache' => $cacheDir,
        ]);
    }

    /**
     * Render every queued template with the merged data and return the
     * concatenated output. The queue and data are cleared afterwards.
     *
     * @return string
     */
    public function render(): string
    {
        try {
            $content = '';
            foreach ($this->views as $view) {
                $content .= $this->twig->render($view, $this->data);
            }

            return $content;
        } finally {
            $this->flush();
        }
    }

    /**
     * Expose the underlying Twig environment so extensions, globals, filters
     * and runtime options can be configured.
     */
    public function getEnvironment(): Environment
    {
        return $this->twig;
    }
}
