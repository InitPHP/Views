# FAQ

### Which adapter should I use?

- **No dependencies, full control, small/medium apps:** [Pure PHP](PurePHP-Adapter.md).
- **Rich templating with layouts, components and directives:** [Blade](Blade-Adapter.md)
  (needs `illuminate/view`).
- **Designer-friendly, sandboxed templates:** [Twig](Twig-Adapter.md) (needs
  `twig/twig`).

All three implement the same interface, so you can start with Pure PHP and
switch later by changing one line at bootstrap. See the comparison table on the
[Home](Home.md#the-adapters-at-a-glance) page.

### Do I have to use the `view()` helper?

No. The helper is a convenience wrapper over the `View` facade. Use the facade
directly if you prefer, or if another `view()` function already exists in your
project (the helper is guarded by `function_exists`, so it yields to an existing
one):

```php
echo View::setView('home')->setData(['title' => 'Hi'])->render();
```

### Why do I get "No view adapter has been registered"?

You called the facade or the `view()` helper before
[`View::via()`](The-View-Facade-and-Helper.md#registering-the-adapter).
Register an adapter once during bootstrap, before any rendering. The error is a
[`ViewException`](Exceptions.md).

### Can I switch engines without changing my code?

Yes — that is the point of the package. Your code calls `view()` or the facade;
only the `View::via(...)` line at bootstrap names the engine. Change that line
and everything else stays the same.

### Does it escape output automatically?

It depends on the adapter:

- **[Pure PHP](PurePHP-Adapter.md)** does **not** auto-escape — escape untrusted
  data yourself with `htmlspecialchars()` (or
  [`initphp/escaper`](https://github.com/InitPHP/Escaper)).
- **[Blade](Blade-Adapter.md)** escapes `{{ }}` output by default.
- **[Twig](Twig-Adapter.md)** auto-escapes by default.

### Why did `view('home.php')` not become `home.php.php`?

The Pure PHP adapter only appends `.php` when it is missing, so `view('home')`
and `view('home.php')` both resolve to `views/home.php`. (This was a real bug in
1.0 and is fixed.)

### Can a view file access the adapter or `$this`?

No. With the [Pure PHP adapter](PurePHP-Adapter.md#data-and-scope-isolation), each
view runs in an isolated scope that sees only its data variables — never `$this`
or the renderer's internals. A data key named `views` simply becomes `$views` in
the template.

### Can I pass an object instead of an array?

Yes. An object's **public** properties become the template data:

```php
$user = new stdClass();
$user->name = 'admin';
echo view('profile', $user);   // $name is available
```

### Can I register more than one adapter at a time?

The facade holds **one** active adapter; `via()` overwrites it. If you need two
engines in the same request, construct the adapters directly and call their
`setView()/setData()/render()` methods instead of going through the facade.

### Why does `View::via(PurePHPAdapter::class)` throw?

`via()` can only instantiate a class name when the adapter has a **no-argument**
constructor. The built-in adapters need directories, so pass a configured
**instance** instead:

```php
View::via(new PurePHPAdapter(__DIR__ . '/views'));
```

The class-name form is meant for your own zero-argument adapters. The error is a
[`ViewAdapterException`](Exceptions.md).

### Does it render to a string or echo directly?

Always a string. `view(...)` and `View::render()` **return** the output; you
decide whether to `echo` it, write it to a PSR-7 response, or use it as an email
body. See [Recipes](Recipes.md).

### Does it work outside a framework?

Yes. There is no framework requirement. The Blade adapter even bootstraps a
standalone Illuminate container for you, so Blade works without a Laravel app —
see [Blade Adapter](Blade-Adapter.md#how-the-standalone-container-is-wired).

### Why must Twig view names include the extension?

Twig resolves templates by their exact filename and does not assume an
extension, so you pass `view('page.html.twig')`. Only the
[Pure PHP adapter](PurePHP-Adapter.md#view-names-and-the-php-extension) adds `.php`
for you.

### Is this a PSR implementation?

No — there is no PSR for view rendering. The package defines its own minimal
[`ViewAdapterInterface`](API-Reference.md#viewadapterinterface) and the exceptions
implement a local [`ViewExceptionInterface`](Exceptions.md) marker.

### Something is still unclear

[Open an issue](https://github.com/InitPHP/Views/issues/new) or start a
[discussion](https://github.com/orgs/InitPHP/discussions) — documentation fixes
are merged eagerly.
