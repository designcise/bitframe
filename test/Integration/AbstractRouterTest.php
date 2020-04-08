<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Integration;

use stdClass;
use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use BitFrame\Router\AbstractRouter;
use BitFrame\Test\Asset\InteropMiddleware;

/**
 * @covers \BitFrame\Router\AbstractRouter
 */
class AbstractRouterTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|AbstractRouter */
    private $router;

    public function setUp(): void
    {
        $this->router = new class extends AbstractRouter {
            private stdClass $route;

            public function __construct()
            {
                $this->route = (object) [];
            }

            public function map($methods, string $path, $handler)
            {
                $this->route->methods = $methods;
                $this->route->path = $path;
                $this->route->handler = $handler;
            }

            public function getRouteData(): stdClass
            {
                return $this->route;
            }
        };
    }

    public function testGet(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->get('/hello/world', $handler);
        $routeData = $this->router->getRouteData();

        $this->assertEquals(['GET'], $routeData->methods);
        $this->assertSame('/hello/world', $routeData->path);
        $this->assertSame($handler, $routeData->handler);
    }

    public function testPost(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->post('/hello/world', $handler);
        $routeData = $this->router->getRouteData();

        $this->assertEquals(['POST'], $routeData->methods);
        $this->assertSame('/hello/world', $routeData->path);
        $this->assertSame($handler, $routeData->handler);
    }

    public function testPut(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->put('/hello/world', $handler);
        $routeData = $this->router->getRouteData();

        $this->assertEquals(['PUT'], $routeData->methods);
        $this->assertSame('/hello/world', $routeData->path);
        $this->assertSame($handler, $routeData->handler);
    }

    public function testPatch(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->patch('/hello/world', $handler);
        $routeData = $this->router->getRouteData();

        $this->assertEquals(['PATCH'], $routeData->methods);
        $this->assertSame('/hello/world', $routeData->path);
        $this->assertSame($handler, $routeData->handler);
    }

    public function testDelete(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->delete('/hello/world', $handler);
        $routeData = $this->router->getRouteData();

        $this->assertEquals(['DELETE'], $routeData->methods);
        $this->assertSame('/hello/world', $routeData->path);
        $this->assertSame($handler, $routeData->handler);
    }

    public function testHead(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->head('/hello/world', $handler);
        $routeData = $this->router->getRouteData();

        $this->assertEquals(['HEAD'], $routeData->methods);
        $this->assertSame('/hello/world', $routeData->path);
        $this->assertSame($handler, $routeData->handler);
    }

    public function testOptions(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->options('/hello/world', $handler);
        $routeData = $this->router->getRouteData();

        $this->assertEquals(['OPTIONS'], $routeData->methods);
        $this->assertSame('/hello/world', $routeData->path);
        $this->assertSame($handler, $routeData->handler);
    }

    public function testAny(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->any('/hello/world', $handler);
        $routeData = $this->router->getRouteData();

        $this->assertEquals(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $routeData->methods);
        $this->assertSame('/hello/world', $routeData->path);
        $this->assertSame($handler, $routeData->handler);
    }

    private function getRouteHandler(): Closure
    {
        return fn ($req, $handler) => $handler->handle($req);
    }
}
