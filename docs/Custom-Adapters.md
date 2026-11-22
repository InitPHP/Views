# Custom Adapters

The three built-in adapters are not special — each implements one small
interface. To add your own engine (a string templater, a Mustache backend, a
test double…), implement
[`ViewAdapterInterface`](API-Reference.md#viewadapterinterface). The easiest path is
to extend [`AdapterAbstract`](API-Reference.md#adapterabstract), which already
implements everything except `render()`.

## The contract

```php
namespace InitPHP\Views\Interfaces;

interface ViewAdapterInterface
{
    public function setView(string ...$views): static;

    public function setData(array|object $data): static;

    public function getData(?string $key = null, mixed $default = null): mixed;

    public function render(): string;
}
```

| Method | Responsibility |
| --- | --- |
| `setView()` | Queue one or more view names, appended in order. Returns `$this`. |
| `setData()` | Merge data (array, or an object's public properties). Returns `$this`. |
| `getData()` | Read one value, or the whole data set when `$key` is `null`. |
| `render()` | Render the queue with the data and return the output. |

## What `AdapterAbstract` gives you

`InitPHP\Views\Adapters\AdapterAbstract` implements `setView()`, `setData()`,
`getData()`, `__toString()` (which calls `render()`), and a protected
`flush()`. Two protected properties hold the queued state:

- `protected array $views` — `list<string>`, the queued view names, in order.
- `protected array $data` — `array<string, mixed>`, the merged data.

You implement `render()`: build the output from `$this->views` and `$this->data`,
then call `flush()` so the instance can be reused. A `finally` block guarantees
the reset even when rendering fails.

## A complete example

This adapter renders in-memory templates, replacing `{{ key }}` placeholders
with data — dependency-free and handy for tests:

```php
<?php

declare(strict_types=1);

namespace App\View;

use InitPHP\Views\Adapters\AdapterAbstract;
use InitPHP\Views\Exceptions\ViewException;

use function sprintf;
use function str_replace;

final class StringAdapter extends AdapterAbstract
{
    /**
     * @param array<string, string> $templates
     */
    public function __construct(private array $templates)
    {
    }

    public function render(): string
    {
        try {
            $output = '';
            foreach ($this->views as $view) {
                if (!isset($this->templates[$view])) {
                    throw new ViewException(sprintf('Unknown view "%s".', $view));
                }
                $output .= $this->interpolate($this->templates[$view], $this->data);
            }

            return $output;
        } finally {
            $this->flush();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function interpolate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{ ' . $key . ' }}', (string) $value, $template);
        }

        return $template;
    }
}
```

Register and use it like any built-in adapter:

```php
use InitPHP\Views\Facade\View;

View::via(new StringAdapter([
    'greeting' => 'Hello, {{ name }}!',
]));

echo view('greeting', ['name' => 'admin']);   // "Hello, admin!"
```

## Implementing the interface directly

If you cannot extend `AdapterAbstract`, implement the four methods yourself.
`setView()` and `setData()` should return `static` so calls can chain, and
`render()` should reset any per-render state before returning.

## Conventions worth following

- **Reset after render.** Clear the queued views and data once `render()`
  completes so the adapter is reusable. `AdapterAbstract::flush()` does this for
  you; call it from a `finally` block.
- **Throw the package exceptions.** Use [`ViewException`](Exceptions.md) for runtime
  failures (missing template, engine error) and
  [`ViewInvalidArgumentException`](Exceptions.md) for bad constructor arguments.
  Both implement [`ViewExceptionInterface`](Exceptions.md), so callers can catch
  everything in one place.
- **Isolate template scope** if you `include` PHP files, so templates cannot
  reach your adapter's internals — see how the
  [Pure PHP adapter](PurePHP-Adapter.md#data-and-scope-isolation) does it.

## See also

- [API Reference](API-Reference.md#adapterabstract)
- [Testing](Testing.md) — using a custom adapter as a fake in your tests.
