<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use BitFrame\Http\MiddlewareDecoratorTrait;
use BitFrame\Factory\HttpFactory;
use BitFrame\Test\Asset\{
    CallableClass,
    HelloWorldMiddleware,
    InteropMiddleware,
    HelloWorldMiddlewareTrait
};
use TypeError;

/**
 * @covers \BitFrame\Http\MiddlewareDecoratorTrait
 */
class MiddlewareDecoratorTraitTest extends TestCase
{
    use HelloWorldMiddlewareTrait;

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
     * @param mixed $value
     */
    public function testUnpackingEmptyValues($value): void
    {
        $this->assertEmpty($this->middlewareDecorator->getUnpackedMiddleware($value));
    }

    public function testUnpackingArrayOfMiddlewares(): void
    {
        $middlewares = [
            $this->getHelloWorldMiddlewareAsPsr15(),
            $this->getHelloWorldMiddlewareAsClosure(),
            new CallableClass(),
            HelloWorldMiddleware::class,
            InteropMiddleware::class . '::staticRun',
            'BitFrame\Test\Asset\helloWorldCallable',
            [new InteropMiddleware(), 'run'],
            [InteropMiddleware::class, 'staticRun'],
        ];

        $unpackedMiddlewares = $this->middlewareDecorator->getUnpackedMiddleware($middlewares);

        foreach ($unpackedMiddlewares as $middleware) {
            $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        }
    }

    public function invalidMiddlewareProvider(): array
    {
        return [
            ['null' => null],
            ['empty_array' => []],
            ['class_unsupported' => new class {}],
            ['callable_array_non_existent_class' => ['NonExistent', 'run']],
            ['callable_array_non_existent_method' => [new InteropMiddleware(), 'nonExistentMethod']],
        ];
    }

    /**
     * @dataProvider invalidMiddlewareProvider
     *
     * @param array|string|callable|MiddlewareInterface $middleware
     */
    public function testGetDecoratedMiddlewareWithUnsupportedMiddlewareType($middleware): void
    {
        $this->expectException(TypeError::class);
        $this->middlewareDecorator->getDecoratedMiddleware($middleware);
    }

    public function nonExistentMiddlewareProvider(): array
    {
        return [
            ['string_nonexistent_static_callable' => 'NonExistent::run'],
            ['string_non_existent_class_or_function_name' => 'NonExistentFunction'],
        ];
    }

    /**
     * @dataProvider nonExistentMiddlewareProvider
     *
     * @param array|string|callable|MiddlewareInterface $middleware
     */
    public function testGetDecoratedMiddlewareWithNonExistentMiddlewareType($middleware): void
    {
        $this->expectException(\Error::class);
        $this->middlewareDecorator->getDecoratedMiddleware($middleware);
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
    public function testGetDecoratedMiddleware($middleware): void
    {
        $this->assertInstanceOf(
            MiddlewareInterface::class,
            $this->middlewareDecorator->getDecoratedMiddleware($middleware)
        );
    }

    public function callablesProvider(): array
    {
        return [
            ['closure' => $this->getHelloWorldMiddlewareAsClosure()],
            ['invokable_class' => new CallableClass()],
            ['string_static_callable' => InteropMiddleware::class . '::staticRun'],
            ['array_object_callable' => [new InteropMiddleware, 'run']],
            ['array_string_callable' => [InteropMiddleware::class, 'staticRun']]
        ];
    }

    /**
     * @dataProvider callablesProvider
     *
     * @param callable $callable
     */
    public function testGetDecoratedCallableMiddleware(callable $callable): void
    {
        $middleware = $this->middlewareDecorator->getDecoratedCallableMiddleware($callable);
        $response = $middleware->process($this->request, $this->getRequestHandlerMock());

        $this->assertSame('Hello World!', (string) $response->getBody());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|RequestHandlerInterface
     */
    private function getRequestHandlerMock()
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
