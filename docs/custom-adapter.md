# Writing a custom adapter

An adapter turns queued view names plus data into a string. To add your own
engine, implement `InitPHP\Views\Interfaces\ViewAdapterInterface`. The easiest
path is to extend `AdapterAbstract`, which already implements everything except
`render()`.

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
| ------ | -------------- |
| `setView()` | Queue one or more view names, appended in order. Returns `$this`. |
| `setData()` | Merge data (array, or an object's public properties). Returns `$this`. |
| `getData()` | Read one value, or the whole data set when `$key` is `null`. |
| `render()` | Render the queue with the data and return the output. |

## Extending `AdapterAbstract` (recommended)

`AdapterAbstract` provides `setView()`, `setData()`, `getData()`, `__toString()`
and a protected `flush()`. Two protected properties hold the queued state:

- `protected array $views` — `list<string>`, the queued view names.
- `protected array $data` — `array<string, mixed>`, the merged data.

You implement `render()`. Render from `$this->views` and `$this->data`, then
call `flush()` so the instance can be reused — a `finally` block guarantees the
reset even when rendering fails.

```php
use InitPHP\Views\Adapters\AdapterAbstract;
use InitPHP\Views\Exceptions\ViewException;

use function sprintf;
use function str_replace;

/**
 * Renders in-memory templates, replacing {{ key }} placeholders with data.
 */
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

Register and use it like any bundled adapter:

```php
use InitPHP\Views\Facade\View;

View::via(new StringAdapter([
    'greeting' => 'Hello, {{ name }}!',
]));

echo view('greeting', ['name' => 'admin']);   // "Hello, admin!"
```

## Implementing the interface directly

If you cannot extend `AdapterAbstract`, implement the four methods yourself.
`setView()` and `setData()` should return `static` so calls can be chained, and
`render()` should reset any per-render state before returning.

## Conventions worth following

- **Reset after render.** Clear queued views and data once `render()` completes
  so the adapter is reusable. `AdapterAbstract::flush()` does this for you.
- **Throw the package exceptions.** Use `ViewException` for runtime failures
  (missing template, engine error) and `ViewInvalidArgumentException` for bad
  constructor arguments. Both implement
  [`ViewExceptionInterface`](exceptions.md), so callers can catch everything in
  one place.
- **Isolate template scope** if you `include` PHP files, so templates cannot
  reach your adapter's internals (see how [PurePHPAdapter](purephp-adapter.md)
  does it).
