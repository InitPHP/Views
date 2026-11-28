# Blade Adapter

`InitPHP\Views\Adapters\BladeAdapter` renders
[Laravel Blade](https://laravel.com/docs/blade) templates **without a full
Laravel application** — it bootstraps a minimal Illuminate container for you.

## Requirements

```bash
composer require illuminate/view
```

The adapter accepts `illuminate/view` `^9.0` through `^13.0`. CI runs the test
suite against 9.0 (lowest dependencies), 12 (PHP 8.2) and 13 (PHP 8.3 or newer);
10 and 11 are not exercised in CI.

## Construction

```php
public function __construct(
    string|array $viewDir,
    string $cacheDir,
    ?\Illuminate\Container\Container $container = null
)
```

- `$viewDir` — a template directory, or an array of directories.
- `$cacheDir` — a writable directory for compiled templates.
- `$container` — optional; a pre-built Illuminate container. When omitted, the
  adapter builds a minimal one itself.

Every directory must exist, or a [`ViewInvalidArgumentException`](Exceptions.md) is
thrown.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\BladeAdapter;

View::via(new BladeAdapter(__DIR__ . '/views', __DIR__ . '/cache'));
```

Multiple template roots:

```php
new BladeAdapter([__DIR__ . '/views', __DIR__ . '/vendor-views'], __DIR__ . '/cache');
```

## Rendering

Blade view names are dotted or slashed and omit the `.blade.php` extension:

```php
echo view('dashboard', ['username' => 'admin']);   // views/dashboard.blade.php
```

`views/dashboard.blade.php`:

```blade
<h1>Welcome, {{ $username }}</h1>
```

Render several views in order:

```php
echo view(['layouts.header', 'pages.home', 'layouts.footer'], ['title' => 'Home']);
```

Blade escapes `{{ }}` output by default — use `{!! !!}` only for trusted HTML.

## Custom directives and conditionals

Register these on the **adapter instance** (keep a reference to it), then render:

```php
$blade = new BladeAdapter(__DIR__ . '/views', __DIR__ . '/cache');
View::via($blade);

$blade->directive('datetime', static function (string $expression): string {
    return "<?php echo ($expression)->format('Y-m-d H:i'); ?>";
});

$blade->if('admin', static fn ($user): bool => $user->isAdmin());
```

```blade
Published: @datetime($post->published_at)

@admin($currentUser)
    <a href="/admin">Admin panel</a>
@endadmin
```

## Other factory methods

The adapter exposes the most common Blade factory methods directly:

| Method | Returns | Purpose |
| --- | --- | --- |
| `make(string $view, array $data = [], array $mergeData = [])` | `View` | Build a view instance from a name. |
| `file(string $path, array $data = [], array $mergeData = [])` | `View` | Build a view instance from an absolute path. |
| `exists(string $view)` | `bool` | Whether a view exists. |
| `share(array\|string $key, mixed $value = null)` | `mixed` | Share data with every view. |
| `composer(array\|string $views, Closure\|string $callback)` | `array` | Register a view composer. |
| `creator(array\|string $views, Closure\|string $callback)` | `array` | Register a view creator. |
| `addNamespace(string $namespace, array\|string $hints)` | `static` | Register a namespace hint. |
| `replaceNamespace(string $namespace, array\|string $hints)` | `static` | Replace a namespace's hints. |
| `directive(string $name, callable $handler)` | `void` | Register a custom directive. |
| `if(string $name, callable $callback)` | `void` | Register a custom `if` conditional. |

Any other method call is forwarded to the underlying
`Illuminate\View\Factory`, so factory methods such as `getShared()`,
`shared()` and `addLocation()` work too:

```php
$blade->share('appName', 'InitPHP');
$blade->getShared();              // ['appName' => 'InitPHP', ...]  (via __call)
```

### View composers

A composer injects data into a view every time it renders:

```php
use Illuminate\Contracts\View\View as BladeView;

$blade->composer('profile', static function (BladeView $view): void {
    $view->with('greeting', 'Hello');
});

echo view('profile');   // $greeting is available even though we passed no data
```

### Namespaces

```php
$blade->addNamespace('admin', __DIR__ . '/admin-views');

echo view('admin::dashboard');   // admin-views/dashboard.blade.php
```

## Working with `View` instances

`make()` returns an `Illuminate\Contracts\View\View`, so you can render it
yourself or pass it around:

```php
$view = $blade->make('card', ['title' => 'Welcome']);
echo $view->render();
```

## How the standalone container is wired

When you don't pass your own container, the adapter:

1. creates a container and binds the filesystem, an event dispatcher and the
   view configuration;
2. registers `Illuminate\View\ViewServiceProvider`;
3. sets itself as the **global** container instance — Blade's engine factories
   resolve their dependencies from it.

To run `illuminate/view` 9 through 13 outside Laravel, the adapter ships two
small internal helpers: a container subclass that provides the `terminating()`
shutdown hook the service provider expects, and a tiny configuration object the
provider reads from. Both are implementation details, not part of the public
API.

## See also

- [Blade documentation](https://laravel.com/docs/blade) (official)
- [Recipes](Recipes.md)
- [API Reference](API-Reference.md#bladeadapter)
