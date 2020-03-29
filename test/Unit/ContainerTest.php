<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Unit;

use PHPUnit\Framework\TestCase;
use BitFrame\Container;
use BitFrame\Test\Asset\NoopService;
use TypeError;
use BitFrame\Exception\{
    ContainerItemNotFoundException,
    ContainerItemFrozenException
};

/**
 * @covers Container
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
            'array_parameter' => ['test', ['deep']],
            'null' => ['null', null],
        ];
    }

    /**
     * @dataProvider valuesProvider
     *
     * @param string $key
     * @param mixed $value
     */
    public function testSetterAndGetter(string $key, $value): void
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

    public function testNonStringIdShouldThrowTypeError(): void
    {
        $container = $this->container;
        $container['foo'] = 'bar';

        $this->expectException(TypeError::class);
        $container->has(456);
    }

    public function testExceptionIsThrownIfValueNotFound(): void
    {
        $container = $this->container;

        $this->expectException(ContainerItemNotFoundException::class);
        $container['test'];
    }
}
