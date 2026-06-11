# Blade adapter

`InitPHP\Views\Adapters\BladeAdapter` renders [Laravel Blade](https://laravel.com/docs/blade)
templates without a full Laravel application.

## Requirements

```bash
composer require illuminate/view
```

`illuminate/view` `^9.0`, `^10.0` and `^11.0` are supported.

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
  adapter builds a minimal container itself.

Every directory must exist, or a `ViewInvalidArgumentException` is thrown.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\BladeAdapter;

View::via(new BladeAdapter(__DIR__ . '/views', __DIR__ . '/cache'));
```

## Rendering

Blade view names are dotted/slashed and omit the `.blade.php` extension:

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

## Custom directives and conditionals

Register these on the adapter instance (keep a reference to it):

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
| ------ | ------- | ------- |
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
`Illuminate\View\Factory`, so factory methods such as `getShared()` work too:

```php
$blade->share('appName', 'InitPHP');
$shared = $blade->getShared();   // ['appName' => 'InitPHP', ...]
```

A view composer that injects data:

```php
use Illuminate\Contracts\View\View as BladeView;

$blade->composer('profile', static function (BladeView $view): void {
    $view->with('greeting', 'Hello');
});
```

## How the standalone container is wired

When you do not pass your own container, the adapter creates one, binds the
filesystem, an event dispatcher and the view configuration, registers
`Illuminate\View\ViewServiceProvider`, and sets itself as the global container
instance (Blade's engine factories resolve their dependencies from it).

To support `illuminate/view` 10 and 11 outside Laravel, the adapter ships two
small internal helpers — a container subclass that provides the `terminating()`
shutdown hook the service provider expects, and a tiny configuration object the
provider reads from. Both are implementation details and are not part of the
public API.
