# Quick Start

Five minutes from `composer require` to a rendered page.

## 1. Register an adapter

Nothing renders until an adapter is registered on the [`View`](The-View-Facade-and-Helper.md)
facade. Do this once, during bootstrap:

```php
<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

View::via(new PurePHPAdapter(__DIR__ . '/views'));
```

`via()` takes a ready-made adapter instance (what you almost always want), or the
class name of an adapter that needs no constructor arguments. Calling the facade
or the helper before registering an adapter throws a
[`ViewException`](Exceptions.md).

## 2. Render a view

The global [`view()`](The-View-Facade-and-Helper.md#the-view-helper) helper queues
the view, attaches the data and returns the output:

```php
echo view('dashboard', ['username' => 'admin']);
```

`views/dashboard.php`:

```php
<h1>Welcome, <?= htmlspecialchars($username) ?></h1>
```

Each data key becomes a local variable in the template. (Pure PHP does not
auto-escape — escape untrusted values yourself.)

## 3. Render several views

A list of names renders in order and concatenates the output — perfect for a
header/content/footer layout:

```php
echo view(['header', 'content', 'footer'], ['title' => 'Home']);
```

The same data is available to every view in the batch.

## 4. Pass an array or an object

`$data` is an associative array, or an object whose **public** properties become
the data:

```php
$user = new stdClass();
$user->username = 'admin';
$user->roles    = ['editor', 'author'];

echo view('profile', $user);   // $username and $roles are available in the view
```

## 5. Switch the engine in one line

Because every adapter shares the same interface, moving from plain PHP to Blade
is a one-line change at bootstrap — your `view()` calls stay the same:

```php
use InitPHP\Views\Adapters\BladeAdapter;

View::via(new BladeAdapter(__DIR__ . '/views', __DIR__ . '/cache'));

// ...the rest of your code is unchanged.
echo view('dashboard', ['username' => 'admin']);
```

See the adapter pages for each engine: [Pure PHP](PurePHP-Adapter.md),
[Blade](Blade-Adapter.md), [Twig](Twig-Adapter.md).

## 6. Without the helper

The helper is optional — the facade exposes the same workflow fluently:

```php
echo View::setView('header', 'footer')
    ->setData(['title' => 'Home'])
    ->render();
```

You can also read merged data back:

```php
View::setData(['title' => 'Home']);
View::getData('title');        // "Home"
View::getData('missing', '—'); // "—"
View::getData();               // ['title' => 'Home']  (everything)
```

## What just happened

1. `via()` stored your adapter as the facade's active instance.
2. `setView()` queued the view name(s); `setData()` merged your data.
3. `render()` produced the output and then **cleared the queue and data**, so the
   adapter is clean for the next call.

## Things worth knowing immediately

- **State resets after every render.** Two `view()` calls never leak into each
  other:

  ```php
  echo view('a', ['x' => 1]);   // renders 'a' with x = 1
  echo view('b', ['x' => 2]);   // renders 'b' with x = 2 only
  ```

- **Pure PHP adds `.php` for you.** `view('home')` and `view('home.php')` both
  resolve to `views/home.php`. Twig is the exception — include the full file
  extension in the name (e.g. `view('home.html.twig')`).
- **Missing views throw.** A Pure PHP view file that does not exist raises a
  [`ViewException`](Exceptions.md).
- **Templates are sandboxed (Pure PHP).** A view file sees only its data, never
  the adapter or `$this`. See [Pure PHP Adapter](PurePHP-Adapter.md#data-and-scope-isolation).

## Next steps

- [The View Facade & Helper](The-View-Facade-and-Helper.md) — the entry points in detail.
- [Recipes](Recipes.md) — layouts, shared data, escaping, controller integration.
- [API Reference](API-Reference.md) — every public member.
