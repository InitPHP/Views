# The View Facade & Helper

Two entry points sit in front of the active adapter: the static `View` facade
and the global `view()` helper. The helper is a thin wrapper over the facade, so
they share one behaviour.

## The `View` facade

`InitPHP\Views\Facade\View` is a static entry point to a single registered
adapter. It is intentionally static-only and cannot be instantiated.

### Registering the adapter

```php
public static function via(string|ViewAdapterInterface $adapter): void
```

`via()` accepts either:

- a ready-to-use adapter **instance** (the usual choice — the built-in adapters
  take constructor arguments), or
- the **class name** of an adapter that can be constructed with no arguments.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

// Instance — required for the bundled adapters
View::via(new PurePHPAdapter(__DIR__ . '/views'));

// Class name — only for adapters with a no-argument constructor
View::via(MyZeroArgAdapter::class);
```

Calling `via()` again replaces the active adapter. It throws a
[`ViewAdapterException`](Exceptions.md) when the argument is a class name that:

- does not exist,
- does not implement [`ViewAdapterInterface`](API-Reference.md#viewadapterinterface), or
- requires constructor arguments that a bare `new $class()` cannot supply
  (pass a configured instance instead).

```php
View::via(PurePHPAdapter::class);
// ViewAdapterException: The adapter "…PurePHPAdapter" cannot be created without
// constructor arguments; pass a configured instance to View::via() instead.
```

### Forwarded calls

Once an adapter is registered, every static call is forwarded to it:

```php
View::setView('header', 'footer');     // queue views, in order
View::setData(['title' => 'Home']);    // merge data
$all = View::getData();                // read all data
$one = View::getData('title', '—');    // read one value, with a default
echo View::render();                   // render the queue and reset
```

`setView()` and `setData()` return the adapter, so they chain:

```php
echo View::setView('dashboard')->setData(['user' => 'admin'])->render();
```

Calling any of these **before** `via()` throws a [`ViewException`](Exceptions.md).

For the [Blade adapter](Blade-Adapter.md), `View` also forwards engine-specific
methods such as `directive()` and any other Blade factory method.

## The `view()` helper

The `view()` function is registered through Composer autoloading and is always
available:

```php
function view(string|array $views, array|object $data = []): string
```

It wraps the facade:

```php
echo view('dashboard', ['username' => 'admin']);

// is exactly:

echo View::setView('dashboard')->setData(['username' => 'admin'])->render();
```

A string renders one view; an array of strings renders several, in order:

```php
echo view(['header', 'content', 'footer'], ['title' => 'Home']);
```

The helper is defined behind a `function_exists('view')` guard, so it will not
collide with a `view()` function your framework may already provide. If another
`view()` is loaded first, the helper simply isn't defined — use the facade
directly in that case.

## State and reuse

`render()` clears the queued views and merged data once it finishes — **even if
a view throws while rendering** — so the same adapter instance can be reused for
independent renders without state leaking between them. (The Pure PHP adapter
resolves view files before it starts rendering; if a file is not found or
resolves outside the view directory, it throws before that reset and the
queued views and data stay in place.)

```php
echo view('a', ['x' => 1]);   // renders 'a' with x = 1
echo view('b', ['x' => 2]);   // renders 'b' with x = 2 only — nothing from the first call
```

`setData()` merges: repeated keys overwrite earlier values within a single
render.

```php
View::setData(['a' => 1, 'b' => 2]);
View::setData(['b' => 3]);     // 'b' is now 3
View::getData();               // ['a' => 1, 'b' => 3]
```

## Reading data — `getData()`

```php
public function getData(?string $key = null, mixed $default = null): mixed
```

- `getData()` or `getData(null)` returns the whole data array.
- `getData('key')` returns the stored value, or `null` if the key is absent.
- `getData('key', $default)` returns `$default` when the key is absent.

A stored `null` is distinct from a missing key — `getData('present', 'x')`
returns `null` if `present` was set to `null`, but `'x'` if it was never set.

## See also

- [API Reference](API-Reference.md#view-facade) — exact signatures.
- [Custom Adapters](Custom-Adapters.md) — what `via()` expects of an adapter.
- [Exceptions](Exceptions.md) — every error the facade can raise.
