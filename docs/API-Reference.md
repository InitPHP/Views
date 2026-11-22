# API Reference

Every public member of `initphp/views`. For narrative explanations, follow the
links into the concept pages.

---

## `View` facade

`InitPHP\Views\Facade\View` — static entry point. See
[The View Facade & Helper](The-View-Facade-and-Helper.md). The facade is `final` and
cannot be instantiated.

### `via()`

```php
public static function via(string|ViewAdapterInterface $adapter): void
```

Register the adapter that backs the facade. Accepts an adapter instance, or the
class name of an adapter constructible with no arguments. **Throws**
[`ViewAdapterException`](#exceptions) if the class is missing, does not implement
[`ViewAdapterInterface`](#viewadapterinterface), or needs constructor arguments.

### Forwarded static calls

Every other static call is forwarded to the registered adapter via
`__callStatic()`, **throwing** [`ViewException`](#exceptions) if no adapter has
been registered:

```php
public static function setView(string ...$views): ViewAdapterInterface
public static function setData(array|object $data): ViewAdapterInterface
public static function getData(?string $key = null, mixed $default = null): mixed
public static function render(): string
```

For the [Blade adapter](Blade-Adapter.md), engine-specific methods such as
`directive()` are forwarded too.

---

## `view()` helper

Global function, registered via Composer autoloading (guarded by
`function_exists`).

```php
function view(string|array $views, array|object $data = []): string
```

Queue one or more views, attach `$data`, render and return the output. A string
renders one view; an array renders several in order. **Throws**
[`ViewException`](#exceptions) if no adapter is registered or a view cannot be
found. See [The View Facade & Helper](The-View-Facade-and-Helper.md#the-view-helper).

---

## `ViewAdapterInterface`

`InitPHP\Views\Interfaces\ViewAdapterInterface` — the contract every adapter
fulfils.

```php
public function setView(string ...$views): static;
```
Queue one or more view names, appended in order. Returns `$this`.

```php
public function setData(array|object $data): static;
```
Merge data exposed to the queued views. An object is reduced to its public
properties; repeated keys overwrite earlier values. Returns `$this`.

```php
public function getData(?string $key = null, mixed $default = null): mixed;
```
Read one value, or — when `$key` is `null` — the whole data array. Returns
`$default` when the key is absent. A stored `null` is distinct from a missing
key.

```php
public function render(): string;
```
Render every queued view and return the concatenated output.

---

## `AdapterAbstract`

`InitPHP\Views\Adapters\AdapterAbstract implements ViewAdapterInterface` — shared
behaviour for adapters. See [Custom Adapters](Custom-Adapters.md).

Implements `setView()`, `setData()`, `getData()` and:

```php
public function __toString(): string
```
Renders the queued views — equivalent to `render()`.

```php
abstract public function render(): string;
```
The one method concrete adapters must implement.

```php
protected function flush(): void
```
Clear the queued views and merged data. Call this after a render so the instance
can be reused.

Protected state available to subclasses:

- `protected array $views` — `list<string>`, queued view names.
- `protected array $data` — `array<string, mixed>`, merged data.

---

## `PurePHPAdapter`

`InitPHP\Views\Adapters\PurePHPAdapter extends AdapterAbstract`. See
[Pure PHP Adapter](PurePHP-Adapter.md).

```php
public function __construct(string $viewDir)
```
`$viewDir` must exist. **Throws** [`ViewInvalidArgumentException`](#exceptions)
otherwise.

```php
public function render(): string
```
Include the queued `.php` files (the extension is added when missing) in an
isolated scope and return their combined output, then reset state. **Throws**
[`ViewException`](#exceptions) if a view file is not found or resolves outside
`$viewDir`.

---

## `BladeAdapter`

`InitPHP\Views\Adapters\BladeAdapter extends AdapterAbstract`. Requires
`illuminate/view`. See [Blade Adapter](Blade-Adapter.md).

```php
public function __construct(
    string|array $viewDir,
    string $cacheDir,
    ?\Illuminate\Container\Container $container = null
)
```
Each directory must exist. **Throws** [`ViewInvalidArgumentException`](#exceptions)
otherwise.

```php
public function render(): string
public function make(string $view, array $data = [], array $mergeData = []): \Illuminate\Contracts\View\View
public function file(string $path, array $data = [], array $mergeData = []): \Illuminate\Contracts\View\View
public function exists(string $view): bool
public function share(array|string $key, mixed $value = null): mixed
public function composer(array|string $views, \Closure|string $callback): array
public function creator(array|string $views, \Closure|string $callback): array
public function addNamespace(string $namespace, array|string $hints): static
public function replaceNamespace(string $namespace, array|string $hints): static
public function directive(string $name, callable $handler): void
public function if(string $name, callable $callback): void
```

Any other call is forwarded to `Illuminate\View\Factory` via `__call()` — for
example `getShared()`, `shared()`, `addLocation()`.

---

## `TwigAdapter`

`InitPHP\Views\Adapters\TwigAdapter extends AdapterAbstract`. Requires
`twig/twig`. See [Twig Adapter](Twig-Adapter.md).

```php
public function __construct(string $viewDir, string $cacheDir)
```
Both directories must exist. **Throws**
[`ViewInvalidArgumentException`](#exceptions) otherwise.

```php
public function render(): string
```
Render each queued template (include the file extension in the name) and return
the combined output, then reset state.

```php
public function getEnvironment(): \Twig\Environment
```
The underlying Twig environment — add extensions, globals and filters through it.

---

## Exceptions

`InitPHP\Views\Exceptions`. See [Exceptions](Exceptions.md).

```php
interface ViewExceptionInterface extends \Throwable
class ViewException extends \RuntimeException implements ViewExceptionInterface
class ViewAdapterException extends ViewException
class ViewInvalidArgumentException extends \InvalidArgumentException implements ViewExceptionInterface
```

Every concrete exception implements `ViewExceptionInterface`, so one `catch`
covers the whole package.
