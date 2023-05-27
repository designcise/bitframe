<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test;

use PHPUnit\Framework\TestCase;
use BitFrame\Container;
use BitFrame\Test\Asset\NoopService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use OutOfBoundsException;
use BitFrame\Exception\{
    ContainerItemNotFoundException,
    ContainerItemFrozenException
};

/**
 * @covers \BitFrame\Container
 */
class ContainerTest extends TestCase
{
    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    public function valuesProvider(): array
    {
        return [
            'parameter' => ['foo', 'bar'],
            'array parameter' => ['test', ['deep']],
            'null' => ['null', null],
        ];
    }

    /**
     * @dataProvider valuesProvider
     *
     * @param string $key
     * @param mixed $value
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testSetterAndGetter(string $key, mixed $value): void
    {
        $container = $this->container;
        $container[$key] = $value;

        $this->assertSame($value, $container->get($key));
    }

    public function closuresProvider(): array
    {
        return [
            'closure' => ['service', static fn () => new NoopService()],
        ];
    }

    /**
     * @dataProvider valuesProvider
     * @dataProvider closuresProvider
     *
     * @param string $key
     * @param mixed $value
     */
    public function testIsset(string $key, $value): void
    {
        $container = $this->container;
        $container[$key] = $value;

        $this->assertTrue(isset($container[$key]));
    }

    /**
     * @dataProvider valuesProvider
     * @dataProvider closuresProvider
     *
     * @param string $key
     * @param mixed $value
     */
    public function testStoredValueCanBeUnset(string $key, $value): void
    {
        $container = $this->container;
        $container[$key] = $value;

        unset($container[$key]);

        $this->expectException(ContainerItemNotFoundException::class);
        $container[$key];
    }

    public function testFactoryServiceCanBeUnset(): void
    {
        $container = $this->container;
        $container['service'] = static fn () => new NoopService();
        $container['service'];

        unset($container['service']);

        $this->expectException(ContainerItemNotFoundException::class);
        $container['service'];
    }

    public function testUnsettingNonExistentKeyShouldThrowException(): void
    {
        $container = $this->container;
        $this->expectException(OutOfBoundsException::class);
        unset($container['non_existent_key']);
    }

    public function testWithClosure(): void
    {
        $container = $this->container;
        $container['service'] = static fn () => new NoopService();

        $this->assertInstanceOf(NoopService::class, $container['service']);
    }

    public function testNonFactoryServicesShouldBeSame(): void
    {
        $container = $this->container;
        $container['service'] = static fn () => new NoopService();

        $serviceOne = $container['service'];
        $this->assertInstanceOf(NoopService::class, $serviceOne);

        $serviceTwo = $container['service'];
        $this->assertInstanceOf(NoopService::class, $serviceTwo);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    public function testFactoryServicesShouldNotBeSame(): void
    {
        $container = $this->container;
        $container['service'] = $this->container->factory(static function () {
            return new NoopService();
        });

        $serviceOne = $container['service'];
        $this->assertInstanceOf(NoopService::class, $serviceOne);

        $serviceTwo = $container['service'];
        $this->assertInstanceOf(NoopService::class, $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testStoredValuesCanBeTraversed(): void
    {
        $container = $this->container;
        $container['foo'] = 'bar';
        $container['baz'] = 'qux';
        $container['test'] = ['deep'];

        $result = [];

        foreach ($container as $key => $val) {
            $result[$key] = $val;
        }

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux', 'test' => ['deep']], $result);
    }

    public function testCannotModifyFrozenKey(): void
    {
        $container = $this->container;
        $container['foo'] = 'bar';
        $container->freeze('foo');

        $this->expectException(ContainerItemFrozenException::class);
        $container['foo'] = 'test';
    }

    public function testCannotDeleteFrozenKey(): void
    {
        $container = $this->container;
        $container['foo'] = 'bar';
        $container->freeze('foo');

        $this->expectException(ContainerItemFrozenException::class);
        unset($container['foo']);
    }

    public function testExceptionIsThrownIfValueNotFound(): void
    {
        $container = $this->container;

        $this->expectException(ContainerItemNotFoundException::class);
        $container['test'];
    }
}
