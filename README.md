# InitPHP Views

A small, adapter-based view rendering layer for PHP. Write your application
against one tiny interface and render with plain PHP, [Blade](https://laravel.com/docs/blade)
or [Twig](https://twig.symfony.com/) — switching engines is a one-line change.

[![CI](https://github.com/InitPHP/Views/actions/workflows/ci.yml/badge.svg)](https://github.com/InitPHP/Views/actions/workflows/ci.yml)
[![Latest Stable Version](http://poser.pugx.org/initphp/views/v)](https://packagist.org/packages/initphp/views) [![Total Downloads](http://poser.pugx.org/initphp/views/downloads)](https://packagist.org/packages/initphp/views) [![License](http://poser.pugx.org/initphp/views/license)](https://packagist.org/packages/initphp/views) [![PHP Version Require](http://poser.pugx.org/initphp/views/require/php)](https://packagist.org/packages/initphp/views)

## Requirements

- PHP 8.0 or higher

The bundled adapters need their engine only when you use them:

| Adapter | Class | Engine | Needs |
| ------- | ----- | ------ | ----- |
| Pure PHP | `InitPHP\Views\Adapters\PurePHPAdapter` | Plain `.php` templates | — (core only) |
| Blade | `InitPHP\Views\Adapters\BladeAdapter` | Laravel Blade | [`illuminate/view`](https://packagist.org/packages/illuminate/view) |
| Twig | `InitPHP\Views\Adapters\TwigAdapter` | Symfony Twig | [`twig/twig`](https://packagist.org/packages/twig/twig) |

## Installation

```bash
composer require initphp/views
```

Then install the engine for the adapter you want (skip this for the Pure PHP adapter):

```bash
composer require illuminate/view   # for BladeAdapter
composer require twig/twig         # for TwigAdapter
```

## Quick start

Register an adapter once through the `View` facade, then render from anywhere
with the global `view()` helper.

```php
require 'vendor/autoload.php';

use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

View::via(new PurePHPAdapter(__DIR__ . '/views'));

echo view('dashboard', ['username' => 'admin']);
```

`views/dashboard.php`:

```php
<h1>Welcome, <?= htmlspecialchars($username) ?></h1>
```

### Rendering multiple views

Pass a list of names to render them in order and concatenate the output:

```php
echo view(['header', 'content', 'footer'], ['title' => 'Home']);
```

### Passing data

`$data` is an associative array, or an object whose public properties are used:

```php
$user = new stdClass();
$user->username = 'admin';

echo view('profile', $user);
```

## The `view()` helper and the `View` facade

The `view()` helper is a thin wrapper over the facade:

```php
function view(string|array $views, array|object $data = []): string;
```

It queues the views, attaches the data and renders — equivalent to:

```php
echo View::setView('header', 'footer')->setData(['title' => 'Home'])->render();
```

Every call you make on `View` is forwarded to the registered adapter:

| Call | Returns | Purpose |
| ---- | ------- | ------- |
| `View::via(string\|ViewAdapterInterface $adapter)` | `void` | Register the adapter that backs the facade. |
| `View::setView(string ...$views)` | adapter | Queue one or more views, in order. |
| `View::setData(array\|object $data)` | adapter | Merge data exposed to the views. |
| `View::getData(?string $key = null, mixed $default = null)` | `mixed` | Read merged data (or everything when `$key` is `null`). |
| `View::render()` | `string` | Render the queue and return the output. |

`View::via()` accepts a ready-made adapter instance, or the class name of an
adapter that can be built with no constructor arguments. Calling the facade
before an adapter is registered throws a `ViewException`.

## Adapters

### Pure PHP adapter

Renders ordinary `.php` files. The `.php` extension is added automatically when
missing, and each file is evaluated in an isolated scope: it receives the data
as local variables and has **no** access to the adapter instance (`$this`).

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

View::via(new PurePHPAdapter(__DIR__ . '/views'));

echo view('dashboard/index', ['username' => 'admin']);
```

A missing view file throws a `ViewException`.

### Blade adapter

Bootstraps a standalone Blade engine — no full Laravel application required.
Install `illuminate/view` first.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\BladeAdapter;

View::via(new BladeAdapter(__DIR__ . '/views', __DIR__ . '/cache'));

echo view('dashboard', ['username' => 'admin']);
```

Both directories must exist. The first argument may also be an array of template
directories. Register custom directives and conditionals on the adapter instance:

```php
$blade = new BladeAdapter(__DIR__ . '/views', __DIR__ . '/cache');
View::via($blade);

$blade->directive('datetime', static function (string $expression): string {
    return "<?php echo ($expression)->format('Y-m-d H:i'); ?>";
});

$blade->if('admin', static fn ($user): bool => $user->isAdmin());
```

The adapter also exposes the most common factory methods — `make()`, `file()`,
`exists()`, `share()`, `composer()`, `creator()`, `addNamespace()` and
`replaceNamespace()` — and forwards any other call to the underlying Blade
factory. See the [Blade documentation](https://laravel.com/docs/blade).

### Twig adapter

Bootstraps a Twig environment. Install `twig/twig` first.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\TwigAdapter;

View::via(new TwigAdapter(__DIR__ . '/views', __DIR__ . '/cache'));

echo view('dashboard.html.twig', ['username' => 'admin']);
```

Both directories must exist. Twig does **not** add a file extension, so include
it in the view name. Use `getEnvironment()` to add extensions, globals or
filters:

```php
$twig = new TwigAdapter(__DIR__ . '/views', __DIR__ . '/cache');
$twig->getEnvironment()->addGlobal('app_name', 'InitPHP');

View::via($twig);
```

## Exceptions

All exceptions implement `InitPHP\Views\Exceptions\ViewExceptionInterface`, so a
single `catch` can handle every failure this package raises.

| Exception | Extends | Thrown when |
| --------- | ------- | ----------- |
| `ViewException` | `RuntimeException` | The facade is used before an adapter is registered, or a view file is missing. |
| `ViewAdapterException` | `ViewException` | `View::via()` is given an invalid adapter. |
| `ViewInvalidArgumentException` | `InvalidArgumentException` | A view or cache directory does not exist. |

```php
use InitPHP\Views\Exceptions\ViewExceptionInterface;

try {
    echo view('dashboard', $data);
} catch (ViewExceptionInterface $e) {
    // handle any InitPHP Views failure
}
```

## Custom adapters

Implement `InitPHP\Views\Interfaces\ViewAdapterInterface` (or extend
`AdapterAbstract`, which already handles queueing and data) and register it with
`View::via()`. See [docs/custom-adapter.md](docs/custom-adapter.md).

## Documentation

Full developer documentation lives in [`docs/`](docs/):

- [Getting started](docs/getting-started.md)
- [The facade and the `view()` helper](docs/facade-and-helper.md)
- [Pure PHP adapter](docs/purephp-adapter.md)
- [Blade adapter](docs/blade-adapter.md)
- [Twig adapter](docs/twig-adapter.md)
- [Writing a custom adapter](docs/custom-adapter.md)
- [Exceptions](docs/exceptions.md)

## Contributing

Bug reports and pull requests are welcome. Please read the org-wide
[contribution guide](https://github.com/InitPHP/.github/blob/main/CONTRIBUTING.md)
first. Run the full check suite locally before opening a PR:

```bash
composer ci   # php-cs-fixer (dry-run) + phpstan + phpunit
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Released under the [MIT License](./LICENSE). Copyright © 2022 InitPHP.
