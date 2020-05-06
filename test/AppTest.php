<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test;

use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Psr\Container\ContainerInterface;
use BitFrame\Factory\HttpFactory;
use BitFrame\App;
use BitFrame\Test\Asset\{
    CallableClass,
    HelloWorldMiddleware,
    InteropMiddleware,
    HelloWorldMiddlewareTrait
};
use InvalidArgumentException;

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

    public function middlewareProvider(): array
    {
        return [
            'psr15' => [$this->getHelloWorldMiddlewareAsPsr15()],
            'closure' => [$this->getHelloWorldMiddlewareAsClosure()],
            'invokable_class' => [new CallableClass()],
            'string class' => [HelloWorldMiddleware::class],
            'string static callable' => [InteropMiddleware::class . '::staticRun'],
            'string function' => ['BitFrame\Test\Asset\helloWorldCallable'],
            'callable array' => [[new InteropMiddleware(), 'run']],
            'callable array uninstantiated' => [[InteropMiddleware::class, 'staticRun']]
        ];
    }

    /**
     * @dataProvider middlewareProvider
     *
     * @param array|string|callable|MiddlewareInterface $middleware
     */
    public function testUseWithDifferentTypesOfMiddleware($middleware): void
    {
        $this->app->use($middleware);
        $appMiddlewares = $this->app->getMiddlewares();

        foreach ($appMiddlewares as $appMiddleware) {
            $this->assertInstanceOf(MiddlewareInterface::class, $appMiddleware);
        }
    }

    public function testCanGetContainerViaHandlerInCallback(): void
    {
        $app = $this->app;
        $container = $app->getContainer();
        $container['foo'] = 'bar';
        $container['baz'] = 'qux';
        $container['test'] = ['deep'];

        $app->run(function ($req, $handler) {
            /** @var ContainerInterface $container */
            $container = $handler->getContainer();

            $this->assertInstanceOf(ContainerInterface::class, $container);
            $this->assertSame('bar', $container['foo']);
            $this->assertSame('qux', $container['baz']);
            $this->assertSame(['deep'], $container['test']);

            return $handler($req);
        });
    }

    public function emptyValuesProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'empty array' => [[]],
            'boolean' => [false],
            'number 0' => [0],
            'float 0.0' => [0.0],
            'string 0' => ['0'],
        ];
    }

    /**
     * @dataProvider emptyValuesProvider
     *
     * @param mixed $middleware
     */
    public function testRunWithoutMiddleware($middleware): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->app->run($middleware);
    }

    public function testConditionallyAddMiddleware(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/');

        $app = $this->app;

        $app->use([
            [
                $this->createHandlerWithContent('#1;'),
                [
                    (false) ? $this->createHandlerWithContent('should be skipped') : []
                ],
            ],
            (false) ? $this->createHandlerWithContent('should be skipped') : [],
            (false) ? $this->createHandlerWithContent('should be skipped') : null,
            $this->createHandlerWithContent('#2;'),
            (false) ? $this->createHandlerWithContent('should be skipped') : null,
            [[[[
                (true) ? $this->createHandlerWithContent('#3;') : null,
                [[], [], []],
            ]]]],
        ]);

        $response = $app->handle($request);

        $this->assertSame('#1;#2;#3;', (string) $response->getBody());
    }

    public function testNestedMiddleware(): void
    {
        $app = $this->app;

        // parent
        $response = $app->run(function($request, $handler) {
            $handler->write('#1;');

            // nested 1-level deep
            $innerResponse1 = $handler->run(static function($request, $handler) {
                $handler->write('#2.1;');

                // nested 2-level deep
                $innerResponse = $handler->run(static function($request, $handler) {
                    $handler->write('#3;');
                    return $handler->handle($request);
                });

                return $handler->handle($request);
            });

            // nested 2-level deep
            $innerResponse2 = $handler->run(static function($request, $handler) {
                $handler->write('#2.2;');
                return $handler->handle($request);
            });

            return $handler->handle($request);
        });

        $this->assertSame('#1;#2.1;#3;#2.2;', (string) $response->getBody());
    }

    public function testNormalRunAndImmediatelyRun(): void
    {
        $app = $this->app;

        $app->use(static function($request, $handler) {
            $handler->write('Normally Run;');
            return $handler->handle($request);
        });

        $immediatelyInvoked = $app->run(static function($request, $handler) {
            $handler->write('Immediately Run;');
            return $handler->handle($request);
        });

        $this->assertSame('Immediately Run;', (string) $immediatelyInvoked->getBody());

        $app->run();

        $this->assertSame('Immediately Run;Normally Run;', (string) $immediatelyInvoked->getBody());
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

    private function createHandlerWithContent(string $content): Closure
    {
        return static function (
            ServerRequestInterface $request,
            RequestHandlerInterface $handler
        ) use ($content): ResponseInterface {
            $handler->write($content);
            return $handler->handle($request);
        };
    }
}
