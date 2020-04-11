<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Unit;

use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use BitFrame\App;
use BitFrame\Factory\HttpFactory;
use BitFrame\Test\Asset\{
    CallableClass,
    HelloWorldMiddlewareTrait,
    HelloWorldMiddleware,
    InteropMiddleware
};
use InvalidArgumentException;

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

    public function middlewareProvider(): array
    {
        return [
            ['psr15' => $this->getHelloWorldMiddlewareAsPsr15()],
            ['closure' => $this->getHelloWorldMiddlewareAsClosure()],
            ['invokable_class' => new CallableClass()],
            ['string_class' => HelloWorldMiddleware::class],
            ['string_static_callable' => InteropMiddleware::class . '::staticRun'],
            ['string_function' => 'BitFrame\Test\Asset\helloWorldCallable'],
            ['callable_array' => [new InteropMiddleware(), 'run']],
            ['callable_array_uninstantiated' => [InteropMiddleware::class, 'staticRun']]
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
                    return $handler->handle($request, $handler);
                });

                return $handler->handle($request, $handler);
            });

            // nested 2-level deep
            $innerResponse2 = $handler->run(static function($request, $handler) {
                $handler->write('#2.2;');
                return $handler->handle($request, $handler);
            });

            return $handler->handle($request, $handler);
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

        $normalInvoke = $app->run();

        $this->assertSame('Immediately Run;Normally Run;', (string) $immediatelyInvoked->getBody());
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