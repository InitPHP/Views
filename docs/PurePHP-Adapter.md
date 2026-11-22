# Pure PHP Adapter

`InitPHP\Views\Adapters\PurePHPAdapter` renders ordinary PHP files. It needs no
extra dependency — just PHP.

## Construction

```php
public function __construct(string $viewDir)
```

`$viewDir` is the base directory of your templates. It must exist, or a
[`ViewInvalidArgumentException`](Exceptions.md) is thrown. A trailing slash is
optional; the path is canonicalised internally.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

View::via(new PurePHPAdapter(__DIR__ . '/views'));
```

## View names and the `.php` extension

The `.php` extension is appended automatically when it is missing, so both of
these resolve to `views/dashboard.php`:

```php
echo view('dashboard');
echo view('dashboard.php');   // not doubled to dashboard.php.php
```

Names may include subdirectories — use forward slashes:

```php
echo view('dashboard/index');   // views/dashboard/index.php
echo view('emails/welcome');    // views/emails/welcome.php
```

A view file that cannot be found throws a [`ViewException`](Exceptions.md).

## Data and scope isolation

Data keys are extracted into local variables for the view file:

```php
echo view('profile', ['username' => 'admin', 'roles' => ['editor', 'author']]);
```

`views/profile.php`:

```php
<h1><?= htmlspecialchars($username) ?></h1>
<ul>
<?php foreach ($roles as $role): ?>
    <li><?= htmlspecialchars($role) ?></li>
<?php endforeach ?>
</ul>
```

Each file is included in an **isolated scope**:

- It sees only the data variables — never the adapter's internals. A data key
  named `views`, `view` or `path` simply becomes `$views`/`$view`/`$path` in the
  template; it cannot clash with the renderer or break iteration.
- It has **no** access to `$this`. View files are templates, not methods of the
  adapter:

  ```php
  // in a view file:
  <?= isset($this) ? 'has $this' : 'no $this' ?>   // → "no $this"
  ```

> **Escaping is your job.** The Pure PHP adapter does not auto-escape. Always run
> untrusted data through `htmlspecialchars()` (or the
> [`initphp/escaper`](https://github.com/InitPHP/Escaper) package) in the
> template.

## Rendering multiple files

```php
echo view(['header', 'main', 'footer'], ['title' => 'Home']);
```

The files are included in order and their output is concatenated. The same data
is available to every file in the batch — a natural fit for a shared layout:

`views/header.php`:

```php
<!doctype html>
<title><?= htmlspecialchars($title) ?></title>
<header>...</header>
```

## Path confinement

Resolved view paths are confined to the base directory. A name that tries to
escape it (for example via `..`) throws a [`ViewException`](Exceptions.md) instead
of reading the file:

```php
view('../../etc/passwd');   // ViewException — not found, or (if the file exists)
                            // resolves outside the view directory
```

This is a safety net. View names should still be treated as developer-controlled
and never built directly from untrusted input.

## Output buffering

Output is captured with PHP output buffering. If a view throws mid-render, the
buffer is discarded cleanly and the exception propagates — your global buffering
level is never left unbalanced, and the adapter's queued state is reset.

## See also

- [Quick Start](Quick-Start.md)
- [Recipes](Recipes.md) — layout composition, partials, escaping.
- [API Reference](API-Reference.md#purephpadapter)
