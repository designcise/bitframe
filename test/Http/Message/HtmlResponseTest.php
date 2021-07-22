<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Http\Message;

use PHPUnit\Framework\TestCase;
use BitFrame\Http\Message\HtmlResponse;
use TypeError;

/**
 * @covers \BitFrame\Http\Message\HtmlResponse
 */
class HtmlResponseTest extends TestCase
{
    public function testConstructorAcceptsHtmlString(): void
    {
        $body = '<html>Lorem ipsum</html>';
        $response = new HtmlResponse($body);
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCanAddStatusAndHeader(): void
    {
        $body = '<html>Not found</html>';
        $status = 404;
        
        $response = (new HtmlResponse($body))
            ->withStatus($status)
            ->withHeader('x-custom', ['foo-bar']);
        
        $this->assertSame(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testStaticCreateWithCustomContentType(): void
    {
        $response = HtmlResponse::create('<html>Test</html>')
            ->withHeader('content-type', 'multipart/form-data');
        
        $this->assertSame('multipart/form-data', $response->getHeaderLine('Content-Type'));
    }

    public function invalidHtmlContentProvider(): array
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
     * @dataProvider invalidHtmlContentProvider
     *
     * @param mixed $body
     */
    public function testRaisesExceptionforNonStringContent($body): void
    {
        $this->expectException(TypeError::class);
        new HtmlResponse($body);
    }
}
