<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests;

use InitPHP\Views\Interfaces\ViewAdapterInterface;
use InitPHP\Views\Tests\Fixtures\StubAdapter;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AdapterAbstractTest extends TestCase
{
    private StubAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new StubAdapter();
    }

    public function testSetViewIsFluent(): void
    {
        self::assertSame($this->adapter, $this->adapter->setView('a'));
        self::assertInstanceOf(ViewAdapterInterface::class, $this->adapter);
    }

    public function testSetViewAppendsInOrder(): void
    {
        $this->adapter->setView('a', 'b')->setView('c');

        self::assertSame(['a', 'b', 'c'], $this->adapter->exposedViews());
    }

    public function testSetDataMergesArrays(): void
    {
        $this->adapter->setData(['a' => 1])->setData(['b' => 2, 'a' => 3]);

        self::assertSame(['a' => 3, 'b' => 2], $this->adapter->exposedData());
    }

    public function testSetDataAcceptsObject(): void
    {
        $data = new stdClass();
        $data->name = 'admin';
        $data->roles = ['editor'];

        $this->adapter->setData($data);

        self::assertSame(['name' => 'admin', 'roles' => ['editor']], $this->adapter->exposedData());
    }

    public function testSetDataOnlyExposesPublicObjectProperties(): void
    {
        $data = new class () {
            public string $visible = 'yes';
            protected string $hidden = 'no';
        };

        $this->adapter->setData($data);

        self::assertSame(['visible' => 'yes'], $this->adapter->exposedData());
    }

    public function testGetDataReturnsEverythingWhenKeyIsNull(): void
    {
        $this->adapter->setData(['a' => 1, 'b' => 2]);

        self::assertSame(['a' => 1, 'b' => 2], $this->adapter->getData());
    }

    public function testGetDataReturnsSingleValue(): void
    {
        $this->adapter->setData(['name' => 'admin']);

        self::assertSame('admin', $this->adapter->getData('name'));
    }

    public function testGetDataReturnsDefaultForMissingKey(): void
    {
        self::assertSame('fallback', $this->adapter->getData('missing', 'fallback'));
        self::assertNull($this->adapter->getData('missing'));
    }

    public function testGetDataDistinguishesStoredNullFromMissing(): void
    {
        $this->adapter->setData(['maybe' => null]);

        self::assertNull($this->adapter->getData('maybe', 'fallback'));
    }

    public function testFlushClearsViewsAndData(): void
    {
        $this->adapter->setView('a')->setData(['x' => 1]);

        $this->adapter->exposedFlush();

        self::assertSame([], $this->adapter->exposedViews());
        self::assertSame([], $this->adapter->exposedData());
    }

    public function testToStringRendersTheAdapter(): void
    {
        self::assertSame('', (string) $this->adapter);
    }
}
