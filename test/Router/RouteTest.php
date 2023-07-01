<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Router;

use PHPUnit\Framework\TestCase;
use BitFrame\Router\Route;

/**
 * @covers \BitFrame\Router\Route
 */
class RouteTest extends TestCase
{
    public function routesProvider(): array
    {
        return [
            'Simple GET route' => [
                ['GET', '/'],
                ['GET', '/'],
            ],
            'Multiple methods with simple route' => [
                [['GET', 'POST'], ''],
                [['GET', 'POST'], ''],
            ],
            'Non-empty route' => [
                ['PUT', '/foo-bar'],
                ['PUT', '/foo-bar'],
            ],
            'Multiple methods with non-empty route' => [
                [['PUT', 'POST'], '/foo-bar'],
                [['PUT', 'POST'], '/foo-bar'],
            ],
        ];
    }

    /**
     * @dataProvider routesProvider
     *
     * @param array $args
     * @param array $expected
     */
    public function testRouteData(array $args, array $expected): void
    {
        $route = new Route(...$args);

        $this->assertSame($expected[0], $route->getMethods());
        $this->assertSame($expected[1], $route->getPath());
    }
}
