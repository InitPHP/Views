# Installation

`initphp/views` is distributed via
[Packagist](https://packagist.org/packages/initphp/views). The core package has
**no runtime dependencies** — only the adapter you choose may need an engine.

## Requirements

| Requirement | Notes |
| --- | --- |
| **PHP `>= 8.2`** | The code uses `declare(strict_types=1)`, `mixed`, union and `static` return types. |
| Composer | For installation and autoloading. |

### Per-adapter engines

| Adapter | Engine package | Tested versions |
| --- | --- | --- |
| [Pure PHP](PurePHP-Adapter.md) | none — PHP core only | — |
| [Blade](Blade-Adapter.md) | [`illuminate/view`](https://packagist.org/packages/illuminate/view) | `^9.0` · `^10.0` · `^11.0` |
| [Twig](Twig-Adapter.md) | [`twig/twig`](https://packagist.org/packages/twig/twig) | `^3.0` |

> The package's own test suite runs against these engine versions (they are
> its `require-dev` ranges); `initphp/views` itself only suggests the engine
> packages and does not constrain them.
>
> You only need the engine for the adapter you actually use. The Pure PHP
> adapter works on any supported PHP install with no extra setup.

## Install

```bash
composer require initphp/views
```

Then install the engine for the adapter you want (skip this for Pure PHP):

```bash
composer require illuminate/view   # for BladeAdapter
composer require twig/twig         # for TwigAdapter
```

Composer registers the PSR-4 namespace `InitPHP\Views\` and autoloads the global
[`view()`](The-View-Facade-and-Helper.md#the-view-helper) helper, so everything is
available as soon as you require `vendor/autoload.php`.

## Verify the install

Drop this script into your project root as `check-views.php`:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

$dir = __DIR__ . '/views';
@mkdir($dir, 0775, true);
file_put_contents($dir . '/check.php', 'InitPHP Views <?= $status ?>');

View::via(new PurePHPAdapter($dir));

echo view('check', ['status' => "is working.\n"]);
```

```bash
php check-views.php
# InitPHP Views is working.
```

## Next steps

- [Quick Start](Quick-Start.md) — register an adapter and render your first views.
- [The View Facade & Helper](The-View-Facade-and-Helper.md) — how the entry points work.
- Pick an adapter: [Pure PHP](PurePHP-Adapter.md), [Blade](Blade-Adapter.md),
  [Twig](Twig-Adapter.md).
