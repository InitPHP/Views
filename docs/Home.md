# InitPHP Views — Wiki

Welcome to the official documentation for **`initphp/views`** — a small,
adapter-based view rendering layer for **PHP 8.2+**. Your application talks to
one tiny interface; an adapter turns queued view names plus data into a string.
Render with plain PHP, [Blade](https://laravel.com/docs/blade) or
[Twig](https://twig.symfony.com/) and switch engines without touching your call
sites.

```bash
composer require initphp/views
```

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

View::via(new PurePHPAdapter(__DIR__ . '/views'));

echo view('dashboard', ['username' => 'admin']);          // one view
echo view(['header', 'content', 'footer'], ['title' => 'Home']); // many, in order
```

Register an adapter once; everything after that — the global `view()` helper and
the `View` facade — flows through it. Swap `PurePHPAdapter` for
[`BladeAdapter`](Blade-Adapter.md) or [`TwigAdapter`](Twig-Adapter.md) and the rest of
your code is unchanged.

## The package in one table

| Symbol | Role |
| --- | --- |
| [`View`](The-View-Facade-and-Helper.md) | Static facade — register an adapter with `via()`, then forward every call to it. |
| [`view()`](The-View-Facade-and-Helper.md#the-view-helper) | Global helper — the one-liner most code uses. |
| [`ViewAdapterInterface`](API-Reference.md#viewadapterinterface) | The contract every adapter fulfils: `setView`, `setData`, `getData`, `render`. |
| [`AdapterAbstract`](Custom-Adapters.md) | Abstract base with the shared queueing/data behaviour; extend it to build your own adapter. |
| [`PurePHPAdapter`](PurePHP-Adapter.md) · [`BladeAdapter`](Blade-Adapter.md) · [`TwigAdapter`](Twig-Adapter.md) | The three built-in adapters. |
| [`ViewException`](Exceptions.md) · [`ViewAdapterException`](Exceptions.md) · [`ViewInvalidArgumentException`](Exceptions.md) | The exception types; all implement [`ViewExceptionInterface`](Exceptions.md). |

## Start here

- **New to the package?** Read [Installation](Installation.md), then
  [Quick Start](Quick-Start.md).
- **How do the facade and helper work?**
  [The View Facade & Helper](The-View-Facade-and-Helper.md).
- **Choosing an engine?** Compare the adapters below, then read its page:
  [Pure PHP](PurePHP-Adapter.md), [Blade](Blade-Adapter.md), [Twig](Twig-Adapter.md).
- **Building your own engine?** [Custom Adapters](Custom-Adapters.md).
- **Looking for a specific method?** The full [API Reference](API-Reference.md).
- **Practical patterns?** [Recipes](Recipes.md).

## The adapters at a glance

| Adapter | Class | Engine | Needs | Best for |
| --- | --- | --- | --- | --- |
| [Pure PHP](PurePHP-Adapter.md) | `Adapters\PurePHPAdapter` | Plain `.php` templates | core only | zero-dependency rendering, small apps, partials |
| [Blade](Blade-Adapter.md) | `Adapters\BladeAdapter` | Laravel Blade | [`illuminate/view`](https://packagist.org/packages/illuminate/view) | rich templating, layouts, directives |
| [Twig](Twig-Adapter.md) | `Adapters\TwigAdapter` | Symfony Twig | [`twig/twig`](https://packagist.org/packages/twig/twig) | sandboxed templates, designer-friendly syntax |

## What you get

- **One interface, three engines.** Your code depends on
  [`ViewAdapterInterface`](API-Reference.md#viewadapterinterface), never on the
  concrete engine. Switching is a one-line change at bootstrap.
- **A tiny surface.** Queue views with `setView()`, attach data with
  `setData()`, render with `render()`. That is the whole model.
- **A global helper.** `view($names, $data)` covers the common case in one call,
  behind a `function_exists()` guard so it never clashes with your framework.
- **Safe Pure PHP rendering.** Templates run in an isolated scope (no access to
  `$this`), data cannot collide with the renderer's internals, and resolved
  paths are [confined to the view directory](PurePHP-Adapter.md#path-confinement).
- **Reusable adapters.** `render()` clears its state afterwards, so one adapter
  instance serves many independent renders without leakage.
- **One catchable exception family.** Every error implements
  [`ViewExceptionInterface`](Exceptions.md).
- **Extensible.** Add your own engine by extending
  [`AdapterAbstract`](Custom-Adapters.md).

## At a glance — behaviour

| You call | You get |
| --- | --- |
| `view('home')` | renders `home` and returns the output |
| `view(['a', 'b'], $data)` | `a` then `b`, concatenated, sharing `$data` |
| `view('profile', $object)` | object's public properties become template data |
| `View::getData('k', 'x')` | the value for `k`, or `"x"` if it is absent |
| `view('home')` _before_ `View::via()` | throws [`ViewException`](Exceptions.md) |
| `view('home.php')` _(Pure PHP)_ | same as `'home'` — the `.php` is not doubled |
| `view('dashboard/index')` _(Pure PHP)_ | `views/dashboard/index.php` |
| `view('missing')` _(Pure PHP)_ | throws [`ViewException`](Exceptions.md) — file not found |
| `view('../secret')` _(Pure PHP)_ | throws [`ViewException`](Exceptions.md) — not found, or outside the view directory |

## Package metadata

- **License**: MIT
- **Minimum PHP**: 8.2
- **Runtime dependencies**: none (core); an engine per adapter you use
- **Packagist**: [`initphp/views`](https://packagist.org/packages/initphp/views)
- **Source**: [github.com/InitPHP/Views](https://github.com/InitPHP/Views)
- **Issues**: [github.com/InitPHP/Views/issues](https://github.com/InitPHP/Views/issues)
- **Discussions**: [github.com/orgs/InitPHP/discussions](https://github.com/orgs/InitPHP/discussions)

If something in this wiki is unclear, wrong, or out of date,
[open an issue](https://github.com/InitPHP/Views/issues/new) — documentation
fixes are merged eagerly.
