# Twig adapter

`InitPHP\Views\Adapters\TwigAdapter` renders [Symfony Twig](https://twig.symfony.com/)
templates.

## Requirements

```bash
composer require twig/twig
```

`twig/twig` `^3.0` is supported.

## Construction

```php
public function __construct(string $viewDir, string $cacheDir)
```

- `$viewDir` — directory holding the Twig templates.
- `$cacheDir` — writable directory for compiled templates.

Both directories must exist, or a `ViewInvalidArgumentException` is thrown.

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

## Configuring the environment

Use `getEnvironment()` to reach the underlying `Twig\Environment` and add
globals, filters, functions or extensions:

```php
$twig = new TwigAdapter(__DIR__ . '/views', __DIR__ . '/cache');

$environment = $twig->getEnvironment();
$environment->addGlobal('app_name', 'InitPHP');
$environment->addFilter(new \Twig\TwigFilter('shout', static fn (string $v): string => strtoupper($v)));

View::via($twig);
```

```twig
{{ app_name }} — {{ 'hello'|shout }}
```

## Caching

The cache directory you pass is handed straight to Twig as its `cache` option,
so compiled templates are written there. Make sure it is writable by the web
server user.
