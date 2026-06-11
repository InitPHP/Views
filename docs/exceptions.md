# Exceptions

All exceptions live in the `InitPHP\Views\Exceptions` namespace and implement
the marker interface `ViewExceptionInterface`, which extends `Throwable`. A
single `catch (ViewExceptionInterface $e)` therefore handles every failure the
package can raise.

## Hierarchy

```
Throwable
├── RuntimeException
│   └── ViewException                 implements ViewExceptionInterface
│       └── ViewAdapterException
└── InvalidArgumentException
    └── ViewInvalidArgumentException  implements ViewExceptionInterface
```

| Exception | Extends | Thrown when |
| --------- | ------- | ----------- |
| `ViewException` | `RuntimeException` | The facade is used before an adapter is registered; a Pure PHP view file is missing; an engine fails at render time. |
| `ViewAdapterException` | `ViewException` | `View::via()` receives an adapter that does not exist, does not implement `ViewAdapterInterface`, or cannot be constructed without arguments. |
| `ViewInvalidArgumentException` | `InvalidArgumentException` | A view or cache directory passed to an adapter constructor does not exist. |

## Catching everything

```php
use InitPHP\Views\Exceptions\ViewExceptionInterface;

try {
    echo view('dashboard', $data);
} catch (ViewExceptionInterface $e) {
    // Any InitPHP Views failure lands here.
    error_log($e->getMessage());
    echo 'The page could not be rendered.';
}
```

## Catching specific failures

Because the concrete classes also extend the native SPL exceptions, you can be
as specific as you like:

```php
use InitPHP\Views\Exceptions\ViewAdapterException;
use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;

try {
    View::via(new PurePHPAdapter('/path/that/does/not/exist'));
} catch (ViewInvalidArgumentException $e) {
    // bad directory
}

try {
    echo view('missing-template');
} catch (ViewException $e) {
    // missing view, or no adapter registered
}
```

## Examples by cause

```php
// No adapter registered yet → ViewException
view('home');

// Unknown adapter class → ViewAdapterException
View::via('App\\Views\\NoSuchAdapter');

// Adapter needs constructor arguments → ViewAdapterException
View::via(PurePHPAdapter::class);

// Missing view directory → ViewInvalidArgumentException
new PurePHPAdapter(__DIR__ . '/nope');

// Missing view file (PurePHPAdapter) → ViewException
view('does/not/exist');
```
