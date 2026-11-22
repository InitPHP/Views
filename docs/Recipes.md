# Recipes

Practical patterns for everyday use. All examples assume an adapter has been
registered with [`View::via()`](The-View-Facade-and-Helper.md#registering-the-adapter).

## Header / content / footer layout

The simplest layout: render three views in one call. They share the same data.

```php
echo view(['layout/header', 'pages/home', 'layout/footer'], [
    'title' => 'Home',
    'user'  => $currentUser,
]);
```

`views/layout/header.php`:

```php
<!doctype html>
<html lang="en">
<head><title><?= htmlspecialchars($title) ?></title></head>
<body>
```

`views/layout/footer.php`:

```php
</body>
</html>
```

## A wrapping layout (content injected into a shell)

When the layout needs the page's HTML *inside* it (not just before/after), render
the inner view first and pass its output to the layout.

`views/layout/main.php`:

```php
<!doctype html>
<title><?= htmlspecialchars($title) ?></title>
<main><?= $content ?></main>
```

```php
$content = view('pages/home', ['user' => $currentUser]);

echo view('layout/main', [
    'title'   => 'Home',
    'content' => $content,   // already-rendered, trusted HTML
]);
```

> The two `view()` calls are independent — the adapter resets between them, so
> the inner data never leaks into the layout.

## Partials and loops (Pure PHP)

Render a partial per item and concatenate:

```php
$rows = '';
foreach ($products as $product) {
    $rows .= view('partials/product-row', ['product' => $product]);
}

echo view('layout/main', ['title' => 'Catalogue', 'content' => $rows]);
```

## Escaping untrusted data (Pure PHP)

The Pure PHP adapter does not auto-escape. Escape in the template, or define a
short helper:

```php
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```

```php
<h1><?= e($title) ?></h1>
```

[Blade](Blade-Adapter.md) (`{{ }}`) and [Twig](Twig-Adapter.md) auto-escape by default.

## Returning a view from a controller

`view()` returns a string, so feed it straight into your response:

```php
final class HomeController
{
    public function index(): string
    {
        return view('pages/home', ['title' => 'Home']);
    }
}
```

With a PSR-7 response (e.g. [`initphp/http`](https://github.com/InitPHP/HTTP)):

```php
$html = view('pages/home', ['title' => 'Home']);
$response->getBody()->write($html);
```

## Choosing the engine by environment

Register one adapter at bootstrap based on configuration; the rest of the app
uses `view()` unchanged.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;
use InitPHP\Views\Adapters\TwigAdapter;

View::via($useTwig
    ? new TwigAdapter(__DIR__ . '/views', __DIR__ . '/cache')
    : new PurePHPAdapter(__DIR__ . '/views'));
```

## Global data on every Blade view

With the [Blade adapter](Blade-Adapter.md), `share()` exposes data to **all** views
without passing it each time:

```php
$blade = new BladeAdapter(__DIR__ . '/views', __DIR__ . '/cache');
$blade->share('appName', 'InitPHP');
$blade->share('year', date('Y'));
View::via($blade);
```

```blade
<footer>{{ $appName }} © {{ $year }}</footer>
```

## Global data with Twig

```php
$twig = new TwigAdapter(__DIR__ . '/views', __DIR__ . '/cache');
$twig->getEnvironment()->addGlobal('appName', 'InitPHP');
View::via($twig);
```

```twig
<footer>{{ appName }}</footer>
```

## Building an email body

Rendering is just string-building — render a template to a variable and hand it
to your mailer (e.g. [`initphp/mailer`](https://github.com/InitPHP/Mailer)):

```php
$body = view('emails/welcome', [
    'name' => $user->name,
    'link' => $activationUrl,
]);

$mailer->setMessage($body)->send();
```

## See also

- [Pure PHP Adapter](PurePHP-Adapter.md) · [Blade Adapter](Blade-Adapter.md) ·
  [Twig Adapter](Twig-Adapter.md)
- [Testing](Testing.md)
