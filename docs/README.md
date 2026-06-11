# InitPHP Views — Documentation

`initphp/views` is a thin, adapter-based rendering layer. Your application
talks to one small interface — `ViewAdapterInterface` — and an adapter turns
queued view names plus data into a string. Swapping the rendering engine
(plain PHP, Blade, Twig, or your own) never touches your call sites.

## Contents

1. [Getting started](getting-started.md) — install, register an adapter, render.
2. [The facade and the `view()` helper](facade-and-helper.md) — how the static
   entry point and the global helper work.
3. Adapters
   - [Pure PHP](purephp-adapter.md)
   - [Blade](blade-adapter.md)
   - [Twig](twig-adapter.md)
4. [Writing a custom adapter](custom-adapter.md) — the `ViewAdapterInterface`
   contract and `AdapterAbstract`.
5. [Exceptions](exceptions.md)

## The 30-second version

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;

View::via(new PurePHPAdapter(__DIR__ . '/views'));

echo view('dashboard', ['username' => 'admin']);          // one view
echo view(['header', 'body', 'footer'], ['title' => 'X']); // many, in order
```

## The model

Every adapter follows the same three-step lifecycle:

1. **Queue** views with `setView()` (names are appended, in order).
2. **Attach** data with `setData()` (arrays or objects; repeated keys overwrite).
3. **Render** with `render()`, which returns the concatenated output and then
   clears the queue and data so the adapter can be reused.

`AdapterAbstract` implements steps 1, 2 and the data accessor for you; a
concrete adapter only implements `render()`.
