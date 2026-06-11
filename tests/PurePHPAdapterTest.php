<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests;

use InitPHP\Views\Adapters\PurePHPAdapter;
use InitPHP\Views\Exceptions\ViewException;
use InitPHP\Views\Exceptions\ViewInvalidArgumentException;
use InitPHP\Views\Tests\Concerns\TempViewDirectory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function ob_get_level;

final class PurePHPAdapterTest extends TestCase
{
    use TempViewDirectory;

    private string $dir;

    private PurePHPAdapter $adapter;

    protected function setUp(): void
    {
        $this->dir = $this->makeTempDir();
        $this->adapter = new PurePHPAdapter($this->dir);
    }

    public function testConstructorRejectsMissingDirectory(): void
    {
        $this->expectException(ViewInvalidArgumentException::class);

        new PurePHPAdapter($this->dir . '/does-not-exist');
    }

    public function testRendersASingleViewWithData(): void
    {
        $this->writeView($this->dir, 'greet.php', 'Hello <?= $name ?>!');

        $output = $this->adapter->setView('greet')->setData(['name' => 'admin'])->render();

        self::assertSame('Hello admin!', $output);
    }

    public function testRendersMultipleViewsInOrder(): void
    {
        $this->writeView($this->dir, 'header.php', '[head]');
        $this->writeView($this->dir, 'body.php', '[body]');
        $this->writeView($this->dir, 'footer.php', '[foot]');

        $output = $this->adapter->setView('header', 'body', 'footer')->render();

        self::assertSame('[head][body][foot]', $output);
    }

    public function testAppendsPhpExtensionWhenMissing(): void
    {
        $this->writeView($this->dir, 'page.php', 'page');

        self::assertSame('page', $this->adapter->setView('page')->render());
    }

    public function testDoesNotDoubleThePhpExtension(): void
    {
        $this->writeView($this->dir, 'page.php', 'page');

        self::assertSame('page', $this->adapter->setView('page.php')->render());
    }

    public function testResolvesViewsInSubdirectories(): void
    {
        $this->writeView($this->dir, 'dashboard/index.php', 'dash');

        self::assertSame('dash', $this->adapter->setView('dashboard/index')->render());
    }

    public function testNormalisesTrailingSeparatorInBaseDirectory(): void
    {
        $this->writeView($this->dir, 'x.php', 'X');
        $adapter = new PurePHPAdapter($this->dir . '/');

        self::assertSame('X', $adapter->setView('x')->render());
    }

    public function testMissingViewFileThrows(): void
    {
        $this->expectException(ViewException::class);

        $this->adapter->setView('nope')->render();
    }

    public function testRejectsPathTraversalOutsideTheViewDirectory(): void
    {
        $base = $this->makeTempDir();
        $this->writeView($base, 'secret.php', 'SECRET');          // base/secret.php
        $this->writeView($base, 'views/ok.php', 'ok');            // base/views/ok.php
        $adapter = new PurePHPAdapter($base . '/views');

        $this->expectException(ViewException::class);

        $adapter->setView('../secret')->render();
    }

    public function testStateIsResetBetweenRenders(): void
    {
        $this->writeView($this->dir, 'greet.php', 'Hi <?= $name ?>');

        $first = $this->adapter->setView('greet')->setData(['name' => 'a'])->render();
        $second = $this->adapter->setView('greet')->setData(['name' => 'b'])->render();

        self::assertSame('Hi a', $first);
        self::assertSame('Hi b', $second, 'A previous render must not leak into the next one.');
    }

    public function testDataKeyNamedViewsDoesNotBreakRendering(): void
    {
        $this->writeView($this->dir, 'page.php', 'value=<?= $views ?>');

        $output = $this->adapter->setView('page')->setData(['views' => 'data-wins'])->render();

        self::assertSame('value=data-wins', $output);
    }

    public function testViewHasNoAccessToTheAdapterInstance(): void
    {
        $this->writeView($this->dir, 'scope.php', '<?= isset($this) ? "has-this" : "no-this" ?>');

        self::assertSame('no-this', $this->adapter->setView('scope')->render());
    }

    public function testToStringRendersQueuedViews(): void
    {
        $this->writeView($this->dir, 'x.php', 'X');

        self::assertSame('X', (string) $this->adapter->setView('x'));
    }

    public function testExceptionInViewDoesNotLeakOutputBuffer(): void
    {
        $this->writeView($this->dir, 'broken.php', '<?php throw new \\RuntimeException("boom");');
        $level = ob_get_level();

        try {
            $this->adapter->setView('broken')->render();
            self::fail('Expected the view exception to propagate.');
        } catch (RuntimeException $e) {
            self::assertSame('boom', $e->getMessage());
        }

        self::assertSame($level, ob_get_level(), 'The output buffer must be balanced after a failed render.');
    }
}
