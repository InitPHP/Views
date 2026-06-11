# Getting started

## Install

```bash
composer require initphp/views
```

The Pure PHP adapter works with the core package alone. The Blade and Twig
adapters need their engine, which you install separately:

```bash
composer require illuminate/view   # BladeAdapter
composer require twig/twig         # TwigAdapter
```

## Register an adapter

Nothing renders until an adapter is registered on the `View` facade. Do this
once, during bootstrap:

```php
require 'vendor/autoload.php';

use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

View::via(new PurePHPAdapter(__DIR__ . '/views'));
```

If you call the facade or the `view()` helper before registering an adapter, a
`ViewException` is thrown.

## Render a view

The global `view()` helper queues the view(s), attaches the data and returns
the rendered string:

```php
echo view('dashboard', ['username' => 'admin']);
```

`views/dashboard.php`:

```php
<h1>Welcome, <?= htmlspecialchars($username) ?></h1>
```

### Multiple views

A list of names is rendered in order and concatenated:

```php
echo view(['header', 'content', 'footer'], ['title' => 'Home']);
```

### Data: arrays and objects

`$data` may be an associative array or an object. For an object, its public
properties become the data:

```php
$user = new stdClass();
$user->username = 'admin';
$user->roles = ['editor'];

echo view('profile', $user);   // $username and $roles are available in the view
```

## Without the helper

The helper is optional — the facade exposes the same workflow fluently:

```php
use InitPHP\Views\Facade\View;

echo View::setView('header', 'footer')
    ->setData(['title' => 'Home'])
    ->render();
```

## Next steps

- [The facade and the `view()` helper](facade-and-helper.md)
- Pick an adapter: [Pure PHP](purephp-adapter.md), [Blade](blade-adapter.md),
  [Twig](twig-adapter.md)
- [Write your own adapter](custom-adapter.md)
