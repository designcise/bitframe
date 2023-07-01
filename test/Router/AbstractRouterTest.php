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

use Closure;
use PHPUnit\Framework\TestCase;
use BitFrame\Router\AbstractRouter;
use BitFrame\Factory\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use BitFrame\Test\Asset\{
    SingleRouteRouter,
    MiddlewareHandler,
    CallableClass,
    HelloWorldMiddlewareTrait,
    HelloWorldMiddleware,
    InteropMiddleware
};

/**
 * @covers \BitFrame\Router\AbstractRouter
 */
class AbstractRouterTest extends TestCase
{
    use HelloWorldMiddlewareTrait;

    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';

    private AbstractRouter $router;

    public function setUp(): void
    {
        $this->router = new SingleRouteRouter();
    }

    public function testGet(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->get('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('GET');

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testPost(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->post('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('POST');

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testPut(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->put('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('PUT');

        $this->assertEquals('PUT', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testPatch(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->patch('/hello/world', $handler);
        $routeData = $this->router->getRouteDataByMethod('PATCH');

        $this->assertEquals('PATCH', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testDelete(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->delete('/hello/world', $handler);
        $routeData = $this->router->getRouteDataByMethod('DELETE');

        $this->assertEquals('DELETE', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testHead(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->head('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('HEAD');

        $this->assertEquals('HEAD', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testOptions(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->options('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('OPTIONS');

        $this->assertEquals('OPTIONS', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testAny(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->any('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('GET');
        $this->assertSame('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('POST');
        $this->assertSame('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PUT');
        $this->assertSame('PUT', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PATCH');
        $this->assertSame('PATCH', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('DELETE');
        $this->assertSame('DELETE', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('OPTIONS');
        $this->assertSame('OPTIONS', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testGroup(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->group('/hello', static function (AbstractRouter $route) use ($handler) {
            $route->get('/world', $handler);
            $route->post('/world', $handler);
            $route->put('/world', $handler);
            $route->patch('/world', $handler);
            $route->delete('/world', $handler);
            $route->options('/world', $handler);
            $route->head('/world', $handler);
        });

        $routeData = $this->router->getRouteDataByMethod('GET');
        $this->assertSame('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('POST');
        $this->assertSame('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PUT');
        $this->assertSame('PUT', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PATCH');
        $this->assertSame('PATCH', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('DELETE');
        $this->assertSame('DELETE', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('OPTIONS');
        $this->assertSame('OPTIONS', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function middlewareProvider(): array
    {
        return [
            'psr15' => [$this->getHelloWorldMiddlewareAsPsr15()],
            'closure' => [$this->getHelloWorldMiddlewareAsClosure()],
            'invokable class' => [new CallableClass()],
            'string class' => [HelloWorldMiddleware::class],
            'string static callable' => [InteropMiddleware::class . '::staticRun'],
            'string function' => ['BitFrame\Test\Asset\helloWorldCallable'],
            'callable array' => [[new InteropMiddleware(), 'run']],
            'callable array uninstantiated' => [[InteropMiddleware::class, 'staticRun']],
        ];
    }

    /**
     * @dataProvider middlewareProvider
     *
     * @param array|string|callable|MiddlewareInterface $middleware
     */
    public function testUse($middleware): void
    {
        $request = HttpFactory::createServerRequest('GET', '/test');

        $this->router->use('GET', $middleware, '/test', static function ($request, $handler) {
            $response = $handler->handle($request);
            $response->getBody()->write('foo bar!');
            return $response;
        });

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $runner = new MiddlewareHandler($routeData['handler'], HttpFactory::getFactory());
        $response = $runner->handle($request);

        $this->assertSame('Hello World!foo bar!', (string) $response->getBody());
    }

    private function getRouteHandler(): Closure
    {
        return fn ($req, $handler) => $handler->handle($req);
    }
}
