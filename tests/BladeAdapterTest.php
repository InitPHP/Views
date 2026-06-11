<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory;
use InitPHP\Views\Adapters\BladeAdapter;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;
use InitPHP\Views\Tests\Concerns\TempViewDirectory;
use PHPUnit\Framework\TestCase;

use function class_exists;

final class BladeAdapterTest extends TestCase
{
    use TempViewDirectory;

    private string $viewDir;

    private string $cacheDir;

    protected function setUp(): void
    {
        if (!class_exists(Factory::class)) {
            self::markTestSkipped('illuminate/view is not installed.');
        }
        $this->viewDir = $this->makeTempDir();
        $this->cacheDir = $this->makeTempDir();
    }

    private function adapter(): BladeAdapter
    {
        return new BladeAdapter($this->viewDir, $this->cacheDir);
    }

    public function testConstructorRejectsMissingViewDirectory(): void
    {
        $this->expectException(ViewInvalidArgumentException::class);

        new BladeAdapter($this->viewDir . '/missing', $this->cacheDir);
    }

    public function testConstructorRejectsMissingCacheDirectory(): void
    {
        $this->expectException(ViewInvalidArgumentException::class);

        new BladeAdapter($this->viewDir, $this->cacheDir . '/missing');
    }

    public function testRendersTemplateWithData(): void
    {
        $this->writeView($this->viewDir, 'hello.blade.php', 'Hello {{ $name }}!');

        $output = $this->adapter()->setView('hello')->setData(['name' => 'admin'])->render();

        self::assertSame('Hello admin!', $output);
    }

    public function testRendersMultipleViewsInOrder(): void
    {
        $this->writeView($this->viewDir, 'a.blade.php', 'A');
        $this->writeView($this->viewDir, 'b.blade.php', 'B');

        $output = $this->adapter()->setView('a', 'b')->render();

        self::assertSame('AB', $output);
    }

    public function testStateIsResetBetweenRenders(): void
    {
        $this->writeView($this->viewDir, 'hi.blade.php', 'Hi {{ $name }}');
        $adapter = $this->adapter();

        $first = $adapter->setView('hi')->setData(['name' => 'a'])->render();
        $second = $adapter->setView('hi')->setData(['name' => 'b'])->render();

        self::assertSame('Hi a', $first);
        self::assertSame('Hi b', $second);
    }

    public function testExistsReportsTemplatePresence(): void
    {
        $this->writeView($this->viewDir, 'present.blade.php', 'x');
        $adapter = $this->adapter();

        self::assertTrue($adapter->exists('present'));
        self::assertFalse($adapter->exists('absent'));
    }

    public function testMakeReturnsAViewContract(): void
    {
        $this->writeView($this->viewDir, 'card.blade.php', 'Card {{ $title }}');
        $view = $this->adapter()->make('card', ['title' => 'A']);

        self::assertInstanceOf(View::class, $view);
        self::assertSame('Card A', $view->render());
    }

    public function testCustomDirectiveIsCompiled(): void
    {
        $this->writeView($this->viewDir, 'greet.blade.php', '@hello');
        $adapter = $this->adapter();
        $adapter->directive('hello', static fn (): string => "<?php echo 'directive!'; ?>");

        self::assertSame('directive!', $adapter->setView('greet')->render());
    }

    public function testCallForwardsUnknownMethodsToTheFactory(): void
    {
        $adapter = $this->adapter();
        $adapter->share('shared-key', 'shared-value');

        $shared = $adapter->getShared();

        self::assertArrayHasKey('shared-key', $shared);
        self::assertSame('shared-value', $shared['shared-key']);
    }

    public function testFileRendersAnAbsolutePath(): void
    {
        $path = $this->writeView($this->viewDir, 'loose.blade.php', 'Loose {{ $name }}');

        self::assertSame('Loose admin', $this->adapter()->file($path, ['name' => 'admin'])->render());
    }

    public function testAddNamespaceResolvesPrefixedViews(): void
    {
        $packageDir = $this->makeTempDir();
        $this->writeView($packageDir, 'widget.blade.php', 'Widget {{ $id }}');

        $adapter = $this->adapter();
        self::assertSame($adapter, $adapter->addNamespace('pkg', $packageDir));

        self::assertSame('Widget 7', $adapter->setView('pkg::widget')->setData(['id' => 7])->render());
    }

    public function testReplaceNamespaceIsFluent(): void
    {
        $packageDir = $this->makeTempDir();
        $adapter = $this->adapter();

        self::assertSame($adapter, $adapter->replaceNamespace('pkg', $packageDir));
    }

    public function testComposerInjectsDataIntoAView(): void
    {
        $this->writeView($this->viewDir, 'profile.blade.php', 'User {{ $name }}');
        $adapter = $this->adapter();
        $adapter->composer('profile', static function (View $view): void {
            $view->with('name', 'composed');
        });

        self::assertSame('User composed', $adapter->setView('profile')->render());
    }

    public function testCreatorInjectsDataIntoAView(): void
    {
        $this->writeView($this->viewDir, 'panel.blade.php', 'Panel {{ $title }}');
        $adapter = $this->adapter();
        $adapter->creator('panel', static function (View $view): void {
            $view->with('title', 'created');
        });

        self::assertSame('Panel created', $adapter->setView('panel')->render());
    }

    public function testCustomIfStatementIsCompiled(): void
    {
        $this->writeView($this->viewDir, 'gate.blade.php', "@admin('admin')\nallowed\n@endadmin");
        $adapter = $this->adapter();
        $adapter->if('admin', static fn (string $role): bool => $role === 'admin');

        self::assertSame('allowed', trim($adapter->setView('gate')->render()));
    }
}
