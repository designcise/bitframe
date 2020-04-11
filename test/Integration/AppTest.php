<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Integration;

use PHPUnit\Framework\TestCase;
use BitFrame\Factory\HttpFactory;
use BitFrame\App;
use BitFrame\Test\Asset\{
    CallableClass,
    HelloWorldMiddleware,
    InteropMiddleware,
    HelloWorldMiddlewareTrait
};
use Psr\Http\Message\ResponseInterface;

use function str_repeat;

/**
 * @covers \BitFrame\App
 */
class AppTest extends TestCase
{
    use HelloWorldMiddlewareTrait;

    private App $app;

    public function setUp(): void
    {
        $this->app = new App();
    }

    public function testHandleMethodResponseShouldHaveEmptyBody(): void
    {
        $this->app->use(HelloWorldMiddleware::class);

        $request = HttpFactory::createServerRequest('HEAD', '/', []);
        $response = $this->app->handle($request);

        $this->assertEmpty((string) $response->getBody());
    }

    public function testRun(): void
    {
        $arrayOfMiddlewares = [
            $this->getHelloWorldMiddlewareAsPsr15(),
            $this->getHelloWorldMiddlewareAsClosure(),
            new CallableClass(),
            HelloWorldMiddleware::class,
            InteropMiddleware::class . '::staticRun',
            'BitFrame\Test\Asset\helloWorldCallable',
            [new InteropMiddleware(), 'run'],
            [InteropMiddleware::class, 'staticRun'],
        ];

        /** @var ResponseInterface $response */
        $response = $this->app->run($arrayOfMiddlewares);

        $this->assertSame(
            str_repeat('Hello World!', count($arrayOfMiddlewares)),
            (string) $response->getBody()
        );
    }

    public function testUseWithArrayOfMiddlewares(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/');

        $arrayOfMiddlewares = [
            $this->getHelloWorldMiddlewareAsPsr15(),
            $this->getHelloWorldMiddlewareAsClosure(),
            new CallableClass(),
            HelloWorldMiddleware::class,
            InteropMiddleware::class . '::staticRun',
            'BitFrame\Test\Asset\helloWorldCallable',
            [new InteropMiddleware(), 'run'],
            [InteropMiddleware::class, 'staticRun'],
        ];

        $app = $this->app;

        $arr = [];
        array_push($arr, $arrayOfMiddlewares, ...$arrayOfMiddlewares);

        $app->use([$arr]);
        $response = $app->handle($request);

        $this->assertSame(
            str_repeat('Hello World!', 16),
            (string) $response->getBody()
        );
    }

    public function testCanWriteToStream(): void
    {
        $this->app->write('Hello world!');

        $this->assertSame('Hello world!', (string) $this->app->getResponse()->getBody());
    }

    public function testIsXhrRequest(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/', [])
            ->withHeader('X-Requested-With', 'XMLHttpRequest');
        $app = new App(null, $request);

        $this->assertTrue($app->isXhrRequest());
    }

    public function testGetRequest(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/', []);
        $app = new App(null, $request);

        $this->assertSame($request, $app->getRequest());
    }

    public function testGetResponse(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/', []);
        $app = new App(null, $request);

        $this->assertSame($request, $app->getRequest());
    }
}