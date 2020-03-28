<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Unit;

use stdClass;
use PHPUnit\Framework\TestCase;
use BitFrame\App;

/**
 * @covers \BitFrame\App
 */
class AppTest extends TestCase
{
    private App $app;

    public function setUp(): void
    {
        $this->app = new App();
    }

    public function testLocalsCanBeSet(): void
    {
        $app = $this->app;
        $app->locals->foo = 'bar';
        $app->locals->baz = 'qux';
        $app->locals->test = ['deep'];

        $this->assertInstanceOf(stdClass::class, $app->locals);
        $this->assertSame(['foo' => 'bar', 'baz' => 'qux', 'test' => ['deep']], $app->getLocals());
    }

    public function testCanCastLocalsToArray(): void
    {
        $app = $this->app;
        $app->locals->foo = 'bar';
        $app->locals->baz = 'qux';
        $app->locals->test = ['deep'];

        $this->assertInstanceOf(stdClass::class, $app->locals);
        $this->assertSame(['foo' => 'bar', 'baz' => 'qux', 'test' => ['deep']], (array)$app->locals);
    }

    public function testStoredLocalsCanBeTraversed(): void
    {
        $app = $this->app;
        $app->locals->foo = 'bar';
        $app->locals->baz = 'qux';
        $app->locals->test = ['deep'];

        $result = [];

        foreach ($app->locals as $key => $val) {
            $result[$key] = $val;
        }

        $this->assertInstanceOf(stdClass::class, $app->locals);
        $this->assertSame(['foo' => 'bar', 'baz' => 'qux', 'test' => ['deep']], $result);
    }

    public function testLocalsCanBeUnset(): void
    {
        $app = $this->app;
        $app->locals->foo = 'bar';
        $app->locals->baz = 'qux';
        $app->locals->test = ['deep'];

        unset($app->locals->test);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $app->getLocals());
    }

    public function testGetLocalsViaHandlerInCallback(): void
    {
        $app = $this->app;
        $app->locals->foo = 'bar';
        $app->locals->baz = 'qux';
        $app->locals->test = ['deep'];

        $app->run(function ($req, $handler) {
            $this->assertInstanceOf(stdClass::class, $handler->locals);
            $this->assertSame(['foo' => 'bar', 'baz' => 'qux', 'test' => ['deep']], $handler->getLocals());
            return $handler($req);
        });
    }

    public function testCanGetLocalsAsArray(): void
    {
        $app = $this->app;
        $app->locals->foo = 'bar';
        $app->locals->baz = 'qux';
        $app->locals->test = ['deep'];

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux', 'test' => ['deep']], $app->getLocals());
    }
}
