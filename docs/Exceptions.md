# Exceptions

All exceptions live in the `InitPHP\Views\Exceptions` namespace and implement
the marker interface `ViewExceptionInterface`, which extends `Throwable`. A
single `catch (ViewExceptionInterface $e)` therefore handles every failure the
package can raise.

## Hierarchy

```
Throwable
└── Exception
    ├── RuntimeException
    │   └── ViewException                 implements ViewExceptionInterface
    │       └── ViewAdapterException
    └── LogicException
        └── InvalidArgumentException
            └── ViewInvalidArgumentException  implements ViewExceptionInterface
```

| Exception | Extends | Thrown when |
| --- | --- | --- |
| `ViewException` | `RuntimeException` | The facade is used before an adapter is registered; a Pure PHP view file is missing or resolves outside the view directory; an engine fails at render time. |
| `ViewAdapterException` | `ViewException` | `View::via()` receives an adapter that does not exist, does not implement [`ViewAdapterInterface`](API-Reference.md#viewadapterinterface), or cannot be constructed without arguments. |
| `ViewInvalidArgumentException` | `InvalidArgumentException` | A view or cache directory passed to an adapter constructor does not exist. |

Because `ViewAdapterException extends ViewException`, catching `ViewException`
also catches adapter-registration failures.

## Catching everything

```php
use InitPHP\Views\Exceptions\ViewExceptionInterface;

try {
    echo view('dashboard', $data);
} catch (ViewExceptionInterface $e) {
    // Any InitPHP Views failure lands here.
    error_log($e->getMessage());
    http_response_code(500);
    echo 'The page could not be rendered.';
}
```

## Catching specific failures

Because the concrete classes also extend the native SPL exceptions, you can be
as specific as you like:

```php
use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;

try {
    View::via(new PurePHPAdapter('/path/that/does/not/exist'));
} catch (ViewInvalidArgumentException $e) {
    // bad directory at construction time
}

try {
    echo view('missing-template');
} catch (ViewException $e) {
    // missing view, path escape, or no adapter registered
}
```

## Examples by cause

```php
// No adapter registered yet → ViewException
view('home');

// Unknown adapter class → ViewAdapterException
View::via('App\\View\\NoSuchAdapter');

// Adapter class needs constructor arguments → ViewAdapterException
View::via(PurePHPAdapter::class);

// Class does not implement the interface → ViewAdapterException
View::via(stdClass::class);

// Missing view directory → ViewInvalidArgumentException
new PurePHPAdapter(__DIR__ . '/nope');

// Missing view file (Pure PHP) → ViewException
view('does/not/exist');

// Path traversal (Pure PHP) → ViewException
view('../secret');
```

## Throwing them from your own adapter

When you [write a custom adapter](Custom-Adapters.md), reuse these types so callers
can handle your failures the same way:

- `ViewException` for runtime problems (missing template, engine error);
- `ViewInvalidArgumentException` for invalid constructor arguments.

## See also

- [API Reference](API-Reference.md#exceptions)
- [The View Facade & Helper](The-View-Facade-and-Helper.md) — which calls raise what.
