<?php
/**
 * BladeAdapter.php
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

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Illuminate\View\ViewServiceProvider;

use function is_string;
use function call_user_func_array;

class BladeAdapter extends AdapterAbstract implements \InitPHP\Views\Interfaces\ViewAdapterInterface
{

    protected Application|ContainerInterface|Container $container;

    /**
     * @var Factory
     */
    private mixed $factory;

    /**
     * @var BladeCompiler
     */
    private mixed $compiler;

    public function __construct(string|array $viewDir, string $cacheDir, ?ContainerInterface $container = null)
    {
        $this->container = $container !== null ? $container : new Container();

        if(is_string($viewDir)){
            $viewDir = [$viewDir];
        }

        $this->setupContainer($viewDir, $cacheDir);

        (new ViewServiceProvider($this->container))->register();

        $this->factory = $this->container->get('view');
        $this->compiler = $this->container->get('blade.compiler');
    }

    public function __call(string $method, array $params)
    {
        return call_user_func_array([$this->factory, $method], $params);
    }

    public function render(): string
    {
        $content = '';
        foreach ($this->views as $view) {
            $content .= $this->make($view, $this->data)->render();
        }
        return $content;
    }

    public function make($view, $data = [], $mergeData = []): View
    {
        return $this->factory->make($view, $data, $mergeData);
    }

    public function if($name, callable $callback)
    {
        $this->compiler->if($name, $callback);
    }

    public function exists($view): bool
    {
        return $this->factory->exists($view);
    }

    public function file($path, $data = [], $mergeData = []): View
    {
        return $this->factory->file($path, $data, $mergeData);
    }

    public function share($key, $value = null)
    {
        return $this->factory->share($key, $value);
    }

    public function composer($views, $callback): array
    {
        return $this->factory->composer($views, $callback);
    }

    public function creator($views, $callback): array
    {
        return $this->factory->creator($views, $callback);
    }

    public function addNamespace($namespace, $hints): self
    {
        $this->factory->addNamespace($namespace, $hints);

        return $this;
    }

    public function replaceNamespace($namespace, $hints): self
    {
        $this->factory->replaceNamespace($namespace, $hints);

        return $this;
    }

    public function directive(string $name, callable $handler)
    {
        $this->compiler->directive($name, $handler);
    }

    private function setupContainer(array $viewPaths, string $cachePath)
    {
        $this->container->bindIf('files', function () {
            return new Filesystem;
        }, true);

        $this->container->bindIf('events', function () {
            return new Dispatcher;
        }, true);

        $this->container->bindIf('config', function () use ($viewPaths, $cachePath) {
            return [
                'view.paths' => $viewPaths,
                'view.compiled' => $cachePath,
            ];
        }, true);

        Facade::setFacadeApplication($this->container);
    }

}
