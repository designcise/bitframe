<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Router;

use PHPUnit\Framework\TestCase;
use BitFrame\Test\Asset\SingleRouteRouter;
use BitFrame\Router\AbstractRouter;
use BitFrame\Router\RouteGroup;

/**
 * @covers \BitFrame\Router\RouteGroup
 */
class RouteGroupTest extends TestCase
{
    private AbstractRouter $router;

    public function setUp(): void
    {
        $this->router = new SingleRouteRouter();
    }

    public function testRoutesCanBeGrouped(): void
    {
        $handler = static function() {};

        new RouteGroup('/foo', function (AbstractRouter $route) use ($handler) {
            $route->get('/bar', $handler);
            $route->post('/bar', $handler);
            $route->put('/bar', $handler);
            $route->patch('/bar', $handler);
            $route->delete('/bar', $handler);
            $route->options('/bar', $handler);
            $route->head('/bar', $handler);
        }, $this->router);

        $routeData = $this->router->getRouteDataByMethod('GET');
        $this->assertSame('GET', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('POST');
        $this->assertSame('POST', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PUT');
        $this->assertSame('PUT', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PATCH');
        $this->assertSame('PATCH', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('DELETE');
        $this->assertSame('DELETE', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('OPTIONS');
        $this->assertSame('OPTIONS', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('HEAD');
        $this->assertSame('HEAD', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testAnyRouteCanBeAddedInRouteGroup(): void
    {
        $handler = static function() {};

        new RouteGroup('/foo', function (AbstractRouter $route) use ($handler) {
            $route->any('/bar', $handler);
        }, $this->router);

        $routeData = $this->router->getRouteDataByMethod('GET');
        $this->assertSame('GET', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('POST');
        $this->assertSame('POST', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PUT');
        $this->assertSame('PUT', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PATCH');
        $this->assertSame('PATCH', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('DELETE');
        $this->assertSame('DELETE', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('OPTIONS');
        $this->assertSame('OPTIONS', $routeData['method']);
        $this->assertSame('/foo/bar', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function groupPathProvider(): array
    {
        return [
            'empty group path' => ['', '/', '/'],
            'empty route path' => ['/', '', '/'],
            'empty group and route path' => ['', '', '/'],
            'group group and route with slashes' => ['/', '/', '/'],
            'group path without leading slash' => ['foo', '', '/foo'],
            'group path with trailing slash' => ['foo/', '', '/foo/'],
            'only group route with trailing slash' => ['foo', '/', '/foo'],
            'group path and route with trailing slash' => ['foo/', '/', '/foo/'],
            'group path with leading slash' => ['/foo', '', '/foo'],
            'group route without slashes' => ['', 'bar', '/bar'],
            'group route with leading slash' => ['', '/bar', '/bar'],
            'group path and route without slashes' => ['foo', 'bar', '/foo/bar'],
            'group path and route with leading slashes' => ['/foo', '/bar', '/foo/bar'],
            'group path and route with trailing slashes' => ['foo/', 'bar/', '/foo/bar/'],
            'group route with trailing slash' => ['/foo', '/bar/', '/foo/bar/'],
            'group route and route with slashes' => ['/foo/', '/bar/', '/foo/bar/'],
        ];
    }

    /**
     * @dataProvider groupPathProvider
     *
     * @param string $groupPath
     * @param string $routePath
     * @param string $expected
     */
    public function testGroupPath(
        string $groupPath,
        string $routePath,
        string $expected
    ): void {
        $handler = static function() {};

        $group = new RouteGroup($groupPath, $handler, $this->router);

        $group->map(['GET'], $routePath, $handler);

        $routeData = $this->router->getRouteDataByMethod('GET');
        $this->assertSame($expected, $routeData['path']);
    }
}