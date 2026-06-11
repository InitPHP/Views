<?php

/**
 * BladeAdapter.php
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

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Illuminate\View\ViewServiceProvider;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;

use function is_dir;

/**
 * Renders templates with the Laravel Blade engine (illuminate/view).
 *
 * The adapter wires up a minimal Illuminate container so Blade can run without
 * a full Laravel application. The Blade {@see Factory} and {@see BladeCompiler}
 * are exposed through dedicated methods and a generic {@see self::__call()}
 * that forwards any other call to the factory.
 *
 * @method array<string, mixed> getShared()                                 Every value shared with the factory.
 * @method mixed                 shared(string $key, mixed $default = null)  Read a single shared value.
 * @method void                  addLocation(string $location)              Register an extra template directory.
 */
class BladeAdapter extends AdapterAbstract
{
    private Container $container;

    private Factory $factory;

    private BladeCompiler $compiler;

    /**
     * @param string|array<int, string> $viewDir   One directory, or a list of directories, holding the Blade templates.
     * @param string                    $cacheDir  Writable directory for the compiled templates.
     * @param Container|null             $container Optional pre-built Illuminate container.
     * @throws ViewInvalidArgumentException If a view directory or the cache directory does not exist.
     */
    public function __construct(string|array $viewDir, string $cacheDir, ?Container $container = null)
    {
        $viewDirs = \is_string($viewDir) ? [$viewDir] : $viewDir;
        foreach ($viewDirs as $dir) {
            if (!is_dir($dir)) {
                throw new ViewInvalidArgumentException('The view directory "' . $dir . '" could not be found.');
            }
        }
        if (!is_dir($cacheDir)) {
            throw new ViewInvalidArgumentException('The cache directory "' . $cacheDir . '" could not be found.');
        }

        $this->container = $container ?? new BladeContainer();

        $this->setupContainer($viewDirs, $cacheDir);

        // Illuminate types ViewServiceProvider for a full Application, but it
        // only consumes container bindings, so a bare Container is enough here.
        /** @phpstan-ignore argument.type */
        (new ViewServiceProvider($this->container))->register();

        $factory = $this->container->get('view');
        $compiler = $this->container->get('blade.compiler');
        \assert($factory instanceof Factory);
        \assert($compiler instanceof BladeCompiler);

        $this->factory = $factory;
        $this->compiler = $compiler;
    }

    /**
     * Forward any otherwise-undefined call to the Blade view factory.
     *
     * @param string             $method
     * @param array<int, mixed>  $params
     * @return mixed
     */
    public function __call(string $method, array $params): mixed
    {
        return $this->factory->{$method}(...$params);
    }

    /**
     * Render every queued view with the merged data and return the
     * concatenated output. The queue and data are cleared afterwards.
     *
     * @return string
     */
    public function render(): string
    {
        try {
            $content = '';
            foreach ($this->views as $view) {
                $content .= $this->make($view, $this->data)->render();
            }

            return $content;
        } finally {
            $this->flush();
        }
    }

    /**
     * Build a Blade view instance from a view name.
     *
     * @param string               $view
     * @param array<string, mixed> $data
     * @param array<string, mixed> $mergeData
     * @return View
     */
    public function make(string $view, array $data = [], array $mergeData = []): View
    {
        return $this->factory->make($view, $data, $mergeData);
    }

    /**
     * Build a Blade view instance from an absolute file path.
     *
     * @param string               $path
     * @param array<string, mixed> $data
     * @param array<string, mixed> $mergeData
     * @return View
     */
    public function file(string $path, array $data = [], array $mergeData = []): View
    {
        return $this->factory->file($path, $data, $mergeData);
    }

    /**
     * Whether the given view exists.
     */
    public function exists(string $view): bool
    {
        return $this->factory->exists($view);
    }

    /**
     * Share a piece of data with every view rendered by the factory.
     *
     * @param array<string, mixed>|string $key
     * @param mixed                        $value
     * @return mixed
     */
    public function share(array|string $key, mixed $value = null): mixed
    {
        return $this->factory->share($key, $value);
    }

    /**
     * Register a view composer.
     *
     * @param array<int, string>|string $views
     * @param Closure|string            $callback
     * @return array<array-key, mixed> The registered composer callbacks.
     */
    public function composer(array|string $views, Closure|string $callback): array
    {
        return $this->factory->composer($views, $callback);
    }

    /**
     * Register a view creator.
     *
     * @param array<int, string>|string $views
     * @param Closure|string            $callback
     * @return array<array-key, mixed> The registered creator callbacks.
     */
    public function creator(array|string $views, Closure|string $callback): array
    {
        return $this->factory->creator($views, $callback);
    }

    /**
     * Add a namespace hint to the view finder.
     *
     * @param string                    $namespace
     * @param array<int, string>|string $hints
     * @return static
     */
    public function addNamespace(string $namespace, array|string $hints): static
    {
        $this->factory->addNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Replace the registered hints for a namespace.
     *
     * @param string                    $namespace
     * @param array<int, string>|string $hints
     * @return static
     */
    public function replaceNamespace(string $namespace, array|string $hints): static
    {
        $this->factory->replaceNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Register a custom Blade directive on the compiler.
     */
    public function directive(string $name, callable $handler): void
    {
        $this->compiler->directive($name, $handler);
    }

    /**
     * Register a custom Blade "if" statement on the compiler.
     */
    public function if(string $name, callable $callback): void
    {
        $this->compiler->if($name, $callback);
    }

    /**
     * Bind the filesystem, event dispatcher and view configuration the Blade
     * factory needs, then point the Facade root at this container.
     *
     * @param array<int, string> $viewPaths
     * @param string             $cachePath
     */
    private function setupContainer(array $viewPaths, string $cachePath): void
    {
        // Illuminate's view engine factories resolve their dependencies from
        // the global container instance, so this one must become that instance.
        Container::setInstance($this->container);

        $this->container->bindIf('files', static fn (): Filesystem => new Filesystem(), true);

        $this->container->bindIf('events', static fn (): Dispatcher => new Dispatcher(), true);

        $this->container->bindIf('config', static fn (): BladeConfig => new BladeConfig([
            'view.paths' => $viewPaths,
            'view.compiled' => $cachePath,
        ]), true);

        // Same Application/Container typing gap as in the constructor: the
        // Facade root only needs a container to resolve bindings from.
        /** @phpstan-ignore argument.type */
        Facade::setFacadeApplication($this->container);
    }
}
