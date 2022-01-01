<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Http\Message;

use ReflectionObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use BitFrame\Http\Message\ResponseDecorator;
use BitFrame\Factory\HttpFactory;

/**
 * @covers \BitFrame\Http\Message\ResponseDecorator
 */
class ResponseDecoratorTest extends TestCase
{
    private ResponseInterface $response;

    public function setUp(): void
    {
        $this->response = new ResponseDecorator(HttpFactory::createResponse());
    }

    public function testSetResponse(): void
    {
        $originalResponse = HttpFactory::createResponse();
        $decoratedResponse = new ResponseDecorator($originalResponse);

        $newResponse = HttpFactory::createResponse();
        $this->response->setResponse($newResponse);

        $decoratedReflection = new ReflectionObject($decoratedResponse);
        $responseProperty = $decoratedReflection->getProperty('response');
        $responseProperty->setAccessible(true);

        $this->assertNotSame($newResponse, $responseProperty->getValue($decoratedResponse));
    }

    public function testWithStatusReturnsNewInstance(): void
    {
        $res = $this->response->withStatus('404');
        $res2 = $res->withStatus('201');

        $this->assertSame(404, $res->getStatusCode());
        $this->assertSame('Not Found', $res->getReasonPhrase());

        $this->assertSame(201, $res2->getStatusCode());
        $this->assertSame('Created', $res2->getReasonPhrase());

        $this->assertNotSame($res2, $res);
    }

    public function testWithHeaderReturnsNewInstance(): void
    {
        $res = $this->response->withHeader('Foo', ['Bar']);
        $res2 = $this->response->withHeader('Baz', 'Qux');

        $this->assertSame(['Foo' => ['Bar']], $res->getHeaders());
        $this->assertSame('Bar', $res->getHeaderLine('Foo'));
        $this->assertSame(['Bar'], $res->getHeader('Foo'));

        $this->assertSame(['Baz' => ['Qux']], $res2->getHeaders());
        $this->assertSame('Qux', $res2->getHeaderLine('Baz'));
        $this->assertSame(['Qux'], $res2->getHeader('Baz'));

        $this->assertNotSame($res2, $res);
    }

    public function testWithAddedHeaderReturnsNewInstance(): void
    {
        $res = $this->response->withHeader('Foo', ['Bar']);
        $res2 = $res->withAddedHeader('foO', 'Baz');

        $this->assertSame(['Foo' => ['Bar']], $res->getHeaders());
        $this->assertSame(['Foo' => ['Bar', 'Baz']], $res2->getHeaders());
        $this->assertSame('Bar, Baz', $res2->getHeaderLine('foo'));
        $this->assertSame(['Bar', 'Baz'], $res2->getHeader('foo'));

        $this->assertNotSame($res2, $res);
    }

    public function testWithAddedHeaderThatDoesNotExistReturnsNewInstance(): void
    {
        $res = $this->response->withHeader('Foo', ['Bar']);
        $res2 = $res->withAddedHeader('nEw', 'Baz');

        $this->assertSame(['Foo' => ['Bar']], $res->getHeaders());
        $this->assertSame(['Foo' => ['Bar'], 'nEw' => ['Baz']], $res2->getHeaders());
        $this->assertSame('Baz', $res2->getHeaderLine('new'));
        $this->assertSame(['Baz'], $res2->getHeader('new'));

        $this->assertNotSame($res2, $res);
    }

    public function testWithoutHeaderThatExistsReturnsNewInstance(): void
    {
        $res = $this->response
            ->withHeader('Foo', ['Bar'])
            ->withHeader('Baz', ['Bam']);
        $res2 = $res->withoutHeader('foO');

        $this->assertTrue($res->hasHeader('foo'));
        $this->assertSame(['Foo' => ['Bar'], 'Baz' => ['Bam']], $res->getHeaders());
        $this->assertFalse($res2->hasHeader('foo'));
        $this->assertSame(['Baz' => ['Bam']], $res2->getHeaders());

        $this->assertNotSame($res2, $res);
    }

    public function testWithProtocolVersionReturnsNewInstance(): void
    {
        $res = $this->response->withProtocolVersion('1.1');
        $res2 = $res->withProtocolVersion('2.0');

        $this->assertSame('1.1', $res->getProtocolVersion());
        $this->assertSame('2.0', $res2->getProtocolVersion());

        $this->assertNotSame($res2, $res);
    }

    public function testWithBodyReturnsNewInstance(): void
    {
        $body = HttpFactory::createStream('foo');
        $res = $this->response->withBody($body);

        $body2 = HttpFactory::createStream('bar');
        $res2 = $res->withBody($body2);

        $this->assertInstanceOf(StreamInterface::class, $res->getBody());
        $this->assertSame('foo', (string) $res->getBody());

        $this->assertInstanceOf(StreamInterface::class, $res2->getBody());
        $this->assertSame('bar', (string) $res2->getBody());

        $this->assertNotSame($res2, $res);
    }
}
