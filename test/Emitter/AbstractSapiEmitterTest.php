<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Emitter;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;
use BitFrame\Factory\HttpFactory;
use BitFrame\Emitter\AbstractSapiEmitter;

abstract class AbstractSapiEmitterTest extends TestCase
{
    protected ServerRequestInterface $request;

    protected AbstractSapiEmitter $emitter;

    protected function setUp(): void
    {
        $this->request = HttpFactory::createServerRequest('GET', '/');
    }

    /**
     * @runInSeparateProcess
     */
    public function testEmitResponse() 
    {
        $status = 202;

        $response = HttpFactory::createResponse($status);
        $response->getBody()->write('Hello World!');

        $this->expectOutputString('Hello World!');
        
        $this->emitter->emit($response);

        $this->assertSame($status, http_response_code());
    }

    /**
     * @runInSeparateProcess
     */
    public function testEmitResponseAsMiddleware() 
    {
        $status = 202;

        $response = HttpFactory::createResponse($status);
        $response->getBody()->write('Hello World!');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->expectOutputString('Hello World!');
        
        $response = $this->emitter->process($this->request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($status, http_response_code());
    }

    /**
     * @runInSeparateProcess
     */
    public function testEmitResponseHeaders()
    {
        $status = 202;

        $response = HttpFactory::createResponse()
            ->withStatus($status)
            ->withAddedHeader('Content-Type', 'text/html')
            ->withAddedHeader('Content-Language', 'en');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);
        
        $response = $this->emitter->process($this->request, $handler);
        $responseHeaders = $response->getHeaders();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($status, http_response_code());
        $this->assertSame(['text/html'], $responseHeaders['Content-Type']);
        $this->assertSame(['en'], $responseHeaders['Content-Language']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCanSetMultipleCookieHeaders()
    {
        $response = HttpFactory::createResponse()
            ->withStatus(200)
            ->withAddedHeader('Set-Cookie', 'foo=bar')
            ->withAddedHeader('Set-Cookie', 'bar=baz');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $response = $this->emitter->process($this->request, $handler);
        $responseHeaders = $response->getHeaders();

        $this->assertSame(['foo=bar', 'bar=baz'], $responseHeaders['Set-Cookie']);
    }
}
