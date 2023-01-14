<?php
/**
 * TwigAdapter.php
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

use InitPHP\Views\Exceptions\ViewInvalidArgumentException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function is_dir;

class TwigAdapter extends AdapterAbstract implements \InitPHP\Views\Interfaces\ViewAdapterInterface
{
    private Environment $twig;

    public function __construct(string $viewDir, string $cacheDir)
    {
        if (!is_dir($viewDir) || !is_dir($cacheDir)) {
            throw new ViewInvalidArgumentException();
        }

        $this->twig = new Environment(new FilesystemLoader($viewDir), [
            'cache'     => $cacheDir
        ]);

        echo $this->twig->render('index.html', ['name' => 'Fabien']);
    }

    public function render(): string
    {
        $content = '';
        foreach ($this->views as $view) {
            $content .= $this->twig->render($view, $this->data);
        }
        return $content;
    }

}
