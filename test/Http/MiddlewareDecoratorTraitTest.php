<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

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
use Error;

/**
 * @covers \BitFrame\Http\MiddlewareDecoratorTrait
 */
class MiddlewareDecoratorTraitTest extends TestCase
{
    use HelloWorldMiddlewareTrait;

    /** @var MiddlewareDecoratorTrait */
    private $middlewareDecorator;

    private ServerRequestInterface $request;

    private ResponseInterface $response;

    public function setUp(): void
    {
        $this->request = HttpFactory::createServerRequest('GET', '/');
        $this->response = HttpFactory::createResponse();
        $this->middlewareDecorator = $this->getMockForTrait(MiddlewareDecoratorTrait::class);
    }

    public function invalidArgsProvider(): array
    {
        return [
            'boolean' => [false],
            'number 0' => [0],
            'float 0.0' => [0.0],
        ];
    }

    /**
     * @dataProvider invalidArgsProvider
     *
     * @param mixed $value
     */
    public function testUnpackingInvalidValues(mixed $value): void
    {
        $this->expectException(TypeError::class);
        $this->middlewareDecorator->unpackMiddleware($value);
    }

    public function emptyValuesProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'empty array' => [[]],
            'string 0' => ['0'],
        ];
    }

    /**
     * @dataProvider emptyValuesProvider
     *
     * @param mixed $value
     */
    public function testUnpackingEmptyValues(mixed $value): void
    {
        $this->assertEmpty($this->middlewareDecorator->unpackMiddleware($value));
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

        $unpackedMiddlewares = $this->middlewareDecorator->unpackMiddleware($middlewares);

        foreach ($unpackedMiddlewares as $middleware) {
            $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        }
    }

    public function invalidMiddlewareProvider(): array
    {
        return [
            'null' => [null],
            'empty array' => [[]],
            'class unsupported' => [new class {}],
            'callable array non-existent class' => [['NonExistent', 'run']],
            'callable array non-existent method' => [[new InteropMiddleware(), 'nonExistentMethod']],
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
            'string non-existent static callable' => ['NonExistent::run'],
            'string non-existent class or function name' => ['NonExistentFunction'],
        ];
    }

    /**
     * @dataProvider nonExistentMiddlewareProvider
     *
     * @param array|string|callable|MiddlewareInterface $middleware
     */
    public function testGetDecoratedMiddlewareWithNonExistentMiddlewareType($middleware): void
    {
        $this->expectException(Error::class);
        $this->middlewareDecorator->getDecoratedMiddleware($middleware);
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
            'callable array uninstantiated' => [[InteropMiddleware::class, 'staticRun']]
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
            'closure' => [$this->getHelloWorldMiddlewareAsClosure()],
            'invokable class' => [new CallableClass()],
            'string static callable' => [InteropMiddleware::class . '::staticRun'],
            'array object callable' => [[new InteropMiddleware, 'run']],
            'array string callable' => [[InteropMiddleware::class, 'staticRun']]
        ];
    }

    /**
     * @dataProvider callablesProvider
     *
     * @param callable $callable
     */
    public function testGetDecoratedCallableMiddleware(callable $callable): void
    {
        $middleware = $this->middlewareDecorator->createDecoratedCallableMiddleware($callable);
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
