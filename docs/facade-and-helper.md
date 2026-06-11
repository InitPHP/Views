# The facade and the `view()` helper

## `View` facade

`InitPHP\Views\Facade\View` is a static entry point to a single registered
adapter. It is intentionally static-only and cannot be instantiated.

### Registering the adapter — `View::via()`

```php
public static function via(string|ViewAdapterInterface $adapter): void
```

`via()` accepts either:

- a ready-to-use adapter **instance**, or
- the **class name** of an adapter that can be constructed with no arguments.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

// Instance (required for the bundled adapters, which take constructor arguments)
View::via(new PurePHPAdapter(__DIR__ . '/views'));

// Class name (only works for adapters with a no-argument constructor)
View::via(MyZeroArgAdapter::class);
```

Calling `via()` again replaces the active adapter.

`via()` throws a `ViewAdapterException` when the class does not exist, does not
implement `ViewAdapterInterface`, or requires constructor arguments that a bare
`new $class()` cannot supply.

### Forwarded calls

Once an adapter is registered, every static call is forwarded to it:

```php
View::setView('header', 'footer');     // queue views
View::setData(['title' => 'Home']);    // attach data
$all  = View::getData();               // read all data
$one  = View::getData('title', '—');   // read one value, with a default
echo View::render();                   // render and reset
```

Calling any of these before `via()` throws a `ViewException`.

For the Blade adapter, `View` also forwards engine-specific methods such as
`directive()` — see the [Blade adapter](blade-adapter.md) page.

## The `view()` helper

The `view()` function is registered through Composer autoloading and is always
available:

```php
function view(string|array $views, array|object $data = []): string
```

It is a convenience wrapper around the facade:

```php
echo view('dashboard', ['username' => 'admin']);

// is equivalent to

echo View::setView('dashboard')->setData(['username' => 'admin'])->render();
```

A string renders one view; an array of strings renders several, in order:

```php
echo view(['header', 'content', 'footer'], ['title' => 'Home']);
```

The helper is defined behind a `function_exists('view')` guard, so it will not
collide with a `view()` function your framework may already provide.

## State and reuse

`render()` clears the queued views and merged data once it finishes (even if a
view throws), so the same adapter instance can be reused for independent
renders without state leaking between them:

```php
echo view('a', ['x' => 1]);   // renders 'a' with x=1
echo view('b', ['x' => 2]);   // renders 'b' with x=2 only — no leftovers from the first call
```
