<?php

/**
 * BitFrame Framework (https://bitframe.designcise.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    Phil Bennett <philipobenito@gmail.com>
 * @copyright Copyright (c) 2017 Phil Bennett <philipobenito@gmail.com>
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Test;

use PHPUnit\Framework\TestCase;
use BitFrame\Router\AbstractRouter;
use BitFrame\Router\RouteGroup;

/**
 * @covers \BitFrame\Router\RouteGroup
 */
class RouteGroupTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|AbstractRouter */
    private $router;

    public function setUp(): void
    {
        $this->router = new class extends AbstractRouter {
            private array $route = [];

            public function map($methods, string $path, $handler)
            {
                foreach ($methods as $method) {
                    $this->route[$method] = [
                        'method' => $method,
                        'path' => $path,
                        'handler' => $handler,
                    ];
                }
            }

            public function getRouteDataByMethod(string $method): array
            {
                return $this->route[$method] ?? [];
            }
        };
    }

    public function testRoutesCanBeGrouped()
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

    public function testAnyRouteCanBeAddedInRouteGroup()
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
}