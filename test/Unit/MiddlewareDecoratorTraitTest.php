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
use Closure;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use BitFrame\Http\MiddlewareDecoratorTrait;
use BitFrame\Factory\HttpFactory;
use BitFrame\Test\Asset\{HelloWorldMiddleware, InteropMiddleware};
use function BitFrame\Test\Asset\helloWorldCallable;

/**
 * @covers \BitFrame\Http\MiddlewareDecoratorTrait
 */
class MiddlewareDecoratorTraitTest extends TestCase
{
    /** @var MiddlewareDecoratorTrait */
    private $middlewareDecorator;
    
    /** @var ServerRequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    public function setUp(): void
    {
        $this->request = HttpFactory::createServerRequest('GET', '/');
        $this->response = HttpFactory::createResponse();
        $this->middlewareDecorator = $this->getMockForTrait(MiddlewareDecoratorTrait::class);
    }

    public function invalidMiddlewareProvider()
    {
        return [
            ['null' => null],
            ['empty_array' => []],
            ['class_unsupported' => new class {}],
            ['callable_array_non_existent_class' => ['NonExistent', 'run']],
            ['callable_array_non_existent_method' => [new InteropMiddleware, 'nonExistentMethod']],
        ];
    }

    /**
     * @dataProvider invalidMiddlewareProvider
     */
    public function testGetDecoratedMiddlewareWithUnsupportedMiddlewareType($middleware)
    {
        $this->expectException(\TypeError::class);
        $this->middlewareDecorator->getDecoratedMiddleware($middleware);
    }

    public function nonExistentMiddlewareProvider()
    {
        return [
            ['string_nonexistent_callable' => 'NonExistent::run'],
            ['string_non_existent_class_or_function_name' => 'NonExistentFunction'],
        ];
    }

    /**
     * @dataProvider nonExistentMiddlewareProvider
     */
    public function testGetDecoratedMiddlewareWithNonExistentMiddlewareType($middleware)
    {
        $this->expectException(\Error::class);
        $this->middlewareDecorator->getDecoratedMiddleware($middleware);
    }

    public function middlewareProvider()
    {
        return [
            ['psr15_middleware' => $this->getHelloWorldMiddlewareAsPsr15()],
            ['closure_middleware' => $this->getHelloWorldMiddlewareAsClosure()],
            ['invokable_class' => $this->getHelloWorldMiddlewareAsInvokableClass()],
            ['string_class_middleware' => HelloWorldMiddleware::class],
            ['string_callable_middleware' => InteropMiddleware::class . '::run'],
            ['string_function_middleware' => 'BitFrame\Test\Asset\helloWorldCallable'],
            ['callable_array_middleware' => [new InteropMiddleware, 'run']],
            ['callable_array_uninstantiated_middleware' => [InteropMiddleware::class, 'run']]
        ];
    }

    /**
     * @dataProvider middlewareProvider
     */
    public function testGetDecoratedMiddleware($middleware)
    {
        $this->assertInstanceOf(
            MiddlewareInterface::class, $this->middlewareDecorator->getDecoratedMiddleware($middleware)
        );
    }

    public function callablesProvider()
    {
        return [
            ['closure' => $this->getHelloWorldMiddlewareAsClosure()],
            ['invokable_class' => new InteropMiddleware()],
            ['string_callable' => InteropMiddleware::class . '::run'],
            ['array_object_callable' => [new InteropMiddleware, 'run']],
            ['array_string_callable' => [InteropMiddleware::class, 'run']]
        ];
    }

    /**
     * @dataProvider callablesProvider
     */
    public function testGetDecoratedCallableMiddleware(callable $callable)
    {
        $middleware = $this->middlewareDecorator->getDecoratedCallableMiddleware($callable);
        $response = $middleware->process($this->request, $this->getRequestHandlerMock());

        $this->assertSame('Hello World!', (string) $response->getBody());
    }

    private function getHelloWorldMiddlewareAsPsr15(): MiddlewareInterface
    {
        return new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request, 
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $response = $handler->handle($request);
                $response->getBody()->write('Hello World!');

                return $response;
            }
        };
    }

    private function getHelloWorldMiddlewareAsInvokableClass(): callable
    {
        return new class {
            public function __invoke(
                ServerRequestInterface $request,
                ResponseInterface $response, 
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $response->getBody()->write('Hello World!');
                return $handler($request, $response);
            }
        };
    }

    private function getHelloWorldMiddlewareAsClosure(): Closure
    {
        return function (
            ServerRequestInterface $request, 
            RequestHandlerInterface $handler
        ): ResponseInterface {
            $response = $handler->handle($request);
            $response->getBody()->write('Hello World!');

            return $response;
        };
    }

    private function getRequestHandlerMock(): RequestHandlerInterface
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->setMethods(['handle', 'getResponse'])
            ->getMock();
        
        $handler
            ->method('getResponse')
            ->willReturn($this->response);
        
        $handler
            ->method('handle')
            ->willReturn($this->response);
        
        return $handler;
    }
}