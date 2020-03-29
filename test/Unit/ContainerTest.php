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

    public function testSetterAndGetter(): void
    {
        $container = $this->container;
        $container['foo'] = 'bar';
        $container['baz'] = 'qux';
        $container['test'] = ['deep'];

        $this->assertSame('bar', $container->get('foo'));
        $this->assertSame('qux', $container->get('baz'));
        $this->assertSame(['deep'], $container->get('test'));
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

    public function testStoredValuesCanBeUnset(): void
    {
        $container = $this->container;
        $container['test'] = ['deep'];

        unset($container['test']);

        $this->expectException(ContainerItemNotFoundException::class);
        $container['test'];
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

    public function testExceptionIsThrownWhenOverwritingFrozenKey(): void
    {
        $container = $this->container;
        $container['foo'] = 'bar';
        $container->freeze('foo');

        $this->expectException(ContainerItemFrozenException::class);
        $container['foo'] = 'test';
    }
}
