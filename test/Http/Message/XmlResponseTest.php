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

use PHPUnit\Framework\TestCase;
use BitFrame\Http\Message\XmlResponse;
use TypeError;

/**
 * @covers \BitFrame\Http\Message\XmlResponse
 */
class XmlResponseTest extends TestCase
{
    public function testConstructorAcceptsBodyAsString(): void
    {
        $body = 'Lorem ipsum';
        $response = new XmlResponse($body);
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCanAddStatusAndHeader(): void
    {
        $body = '<test>XML</test>';
        $status = 404;
        
        $response = (new XmlResponse($body))
            ->withStatus($status)
            ->withHeader('x-custom', ['foo-bar']);
        
        $this->assertSame(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertSame('application/xml; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testStaticCreateWithCustomContentType(): void
    {
        $response = XmlResponse::create('<test>XML</test>')
            ->withHeader('content-type', 'application/xml-dtd');
        
        $this->assertSame('application/xml-dtd', $response->getHeaderLine('Content-Type'));
    }

    public function invalidContentProvider(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'zero' => [0],
            'int' => [1],
            'zero-float' => [0.0],
            'float' => [1.1],
            'array' => [['php://temp']],
            'object' => [(object) ['php://temp']],
        ];
    }

    /**
     * @dataProvider invalidContentProvider
     *
     * @param mixed $body
     */
    public function testRaisesExceptionforNonStringContent($body): void
    {
        $this->expectException(TypeError::class);
        new XmlResponse($body);
    }
}
