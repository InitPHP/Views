# Twig Adapter

`InitPHP\Views\Adapters\TwigAdapter` renders
[Symfony Twig](https://twig.symfony.com/) templates.

## Requirements

```bash
composer require twig/twig
```

The package's test suite runs against `twig/twig` `^3.0`.

## Construction

```php
public function __construct(string $viewDir, string $cacheDir)
```

- `$viewDir` — the directory holding the Twig templates.
- `$cacheDir` — a writable directory for compiled templates.

Both directories must exist, or a [`ViewInvalidArgumentException`](Exceptions.md) is
thrown.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\TwigAdapter;

View::via(new TwigAdapter(__DIR__ . '/views', __DIR__ . '/cache'));
```

## Rendering

Twig does **not** add a file extension — include it in the view name exactly as
the file is named:

```php
echo view('dashboard.html.twig', ['username' => 'admin']);
```

`views/dashboard.html.twig`:

```twig
<h1>Welcome, {{ username }}</h1>
```

Render several templates in order:

```php
echo view(['header.twig', 'content.twig', 'footer.twig'], ['title' => 'Home']);
```

Twig auto-escapes output according to its configuration, which is a key
difference from the [Pure PHP adapter](PurePHP-Adapter.md).

## Configuring the environment

Use `getEnvironment()` to reach the underlying `Twig\Environment` and add
globals, filters, functions or extensions:

```php
public function getEnvironment(): \Twig\Environment
```

```php
use Twig\TwigFilter;

$twig = new TwigAdapter(__DIR__ . '/views', __DIR__ . '/cache');

$environment = $twig->getEnvironment();
$environment->addGlobal('app_name', 'InitPHP');
$environment->addFilter(new TwigFilter('shout', static fn (string $v): string => strtoupper($v)));

View::via($twig);
```

```twig
{{ app_name }} — {{ 'hello'|shout }}   {# InitPHP — HELLO #}
```

## Caching

The cache directory you pass is handed straight to Twig as its `cache` option,
so compiled templates are written there. Make sure it is writable by the web
server user. To iterate on templates during development, you can enable Twig's
auto-reload through the environment:

```php
$twig->getEnvironment()->enableAutoReload();
```

## See also

- [Twig documentation](https://twig.symfony.com/doc/3.x/) (official)
- [Recipes](Recipes.md)
- [API Reference](API-Reference.md#twigadapter)
