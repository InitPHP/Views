# Testing

How to test code that renders views, and how the package itself is tested.

## Testing your own code

Code that calls `view()` or the `View` facade depends on whichever adapter is
registered. You have two good options.

### Option 1 — render real templates with the Pure PHP adapter

Point `PurePHPAdapter` at a fixtures directory and assert on the output. This
exercises the real rendering path.

```php
use InitPHP\Views\Facade\View;
use InitPHP\Views\Adapters\PurePHPAdapter;
use PHPUnit\Framework\TestCase;

final class HomeRenderTest extends TestCase
{
    protected function setUp(): void
    {
        View::via(new PurePHPAdapter(__DIR__ . '/fixtures/views'));
    }

    public function testHomeShowsTheTitle(): void
    {
        $html = view('home', ['title' => 'Welcome']);

        self::assertStringContainsString('Welcome', $html);
    }
}
```

### Option 2 — register a fake adapter

To assert *what* was rendered rather than the produced HTML, register a small
fake that records the views and data it received. Any class implementing
[`ViewAdapterInterface`](API-Reference.md#viewadapterinterface) (or extending
[`AdapterAbstract`](Custom-Adapters.md)) works:

```php
use InitPHP\Views\Adapters\AdapterAbstract;

use function implode;

final class FakeAdapter extends AdapterAbstract
{
    /** @var list<array{views: list<string>, data: array<string, mixed>}> */
    public array $renders = [];

    public function render(): string
    {
        $this->renders[] = ['views' => $this->views, 'data' => $this->data];
        $output = implode(',', $this->views);
        $this->flush();

        return $output;
    }
}
```

```php
use InitPHP\Views\Facade\View;

public function testItRendersTheProfileViewWithTheUser(): void
{
    $fake = new FakeAdapter();
    View::via($fake);

    (new ProfileController())->show($user);

    self::assertSame('profile', $fake->renders[0]['views'][0]);
    self::assertSame($user->id, $fake->renders[0]['data']['id']);
}
```

See [Custom Adapters](Custom-Adapters.md) for the in-memory `StringAdapter`, which
also makes a convenient test double.

## Resetting the facade between tests

The facade holds the registered adapter in a static property. Because
[`View::via()`](The-View-Facade-and-Helper.md#registering-the-adapter)
**overwrites** it, calling `via()` in each test's `setUp()` is all you need to
isolate tests from one another.

To test the *unregistered* state — that calling the facade before `via()` throws
[`ViewException`](Exceptions.md) — run that test in a separate process so the static
starts empty:

```php
/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
public function testRenderingWithoutAnAdapterThrows(): void
{
    $this->expectException(\InitPHP\Views\Exceptions\ViewException::class);

    View::render();
}
```

## Temporary view directories

When testing file-based adapters, create templates under the system temp
directory and clean them up afterwards:

```php
protected function setUp(): void
{
    $this->dir = sys_get_temp_dir() . '/views_' . uniqid('', true);
    mkdir($this->dir, 0777, true);
    file_put_contents($this->dir . '/home.php', 'Hi <?= $name ?>');

    View::via(new PurePHPAdapter($this->dir));
}
```

## How the package is tested

`initphp/views` ships a PHPUnit suite covering the abstract adapter, all three
built-in adapters (including Blade and Twig against their real engines), the
facade, the helper and the exception hierarchy. Continuous integration runs
PHP-CS-Fixer, PHPStan at the maximum level and PHPUnit across PHP 8.2–8.5 with
highest dependencies, plus a lowest-dependencies run on PHP 8.2. Run the same checks locally:

```bash
composer ci   # php-cs-fixer (dry-run) + phpstan + phpunit
```

## See also

- [Custom Adapters](Custom-Adapters.md)
- [Exceptions](Exceptions.md)
