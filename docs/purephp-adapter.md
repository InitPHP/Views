# Pure PHP adapter

`InitPHP\Views\Adapters\PurePHPAdapter` renders ordinary PHP files. It needs no
extra dependency.

## Construction

```php
public function __construct(string $viewDir)
```

`$viewDir` is the base directory of your templates. It must exist, or a
`ViewInvalidArgumentException` is thrown. A trailing slash is optional.

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
echo view('dashboard.php');
```

Names may include subdirectories (use forward slashes):

```php
echo view('dashboard/index');   // views/dashboard/index.php
```

A view file that cannot be found throws a `ViewException`.

### Path confinement

Resolved view paths are confined to the base directory. A name that escapes it
(for example via `..`) throws a `ViewException` instead of reading the file:

```php
view('../../etc/passwd');   // ViewException — resolves outside the view directory
```

This is a safety net; view names should still be treated as developer-controlled
and not built directly from untrusted input.

## Data and scope isolation

Data keys are extracted into local variables for the view file:

```php
echo view('profile', ['username' => 'admin', 'roles' => ['editor']]);
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
  named `views` simply becomes `$views` in the template; it cannot clash with
  the renderer.
- It has **no** access to `$this`. View files are templates, not methods of the
  adapter.

Always escape untrusted data yourself (`htmlspecialchars()`); the Pure PHP
adapter does not auto-escape.

## Rendering multiple files

```php
echo view(['header', 'main', 'footer'], ['title' => 'Home']);
```

The files are included in order and their output is concatenated. The same data
is available to every file in the batch.

## Output buffering

Output is captured with PHP output buffering. If a view throws, the buffer is
discarded cleanly and the exception propagates — your global buffering level is
never left unbalanced.
