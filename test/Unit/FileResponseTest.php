<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use BitFrame\Factory\HttpFactory;
use BitFrame\Http\Message\FileResponse;
use TypeError;
use InvalidArgumentException;

/**
 * @covers \BitFrame\Http\Message\FileResponse
 */
class FileResponseTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';

    
    public function testConstructorAcceptsStringFileName()
    {
        $file = self::ASSETS_DIR . 'test.txt';
        $mimeType = \mime_content_type($file);
        $body = 'test';

        $response = new FileResponse($file);
        
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($mimeType, $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorAcceptsResource()
    {
        $stream = fopen("php://temp/maxmemory:1024", 'r+');
        fputs($stream, 'test');
        $body = 'test';

        $response = new FileResponse($stream);

        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorAcceptsStreamInterfaceObject()
    {
        $stream = HttpFactory::createStream('test');
        $body = 'test';

        $response = new FileResponse($stream);

        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }

    public function testCanAddStatusAndHeader()
    {
        $stream = HttpFactory::createStream('test');

        $body = 'test';
        $status = 404;
        
        $response = (new FileResponse($stream))
            ->withStatus($status)
            ->withHeader('Content-Type', 'foo/file');
        
        $this->assertSame('foo/file', $response->getHeaderLine('content-type'));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testStaticCreateFromStringWithCustomContentType()
    {
        $response = FileResponse::fromPath(self::ASSETS_DIR . 'test.txt')
            ->withHeader('content-type', 'foo/file');
        
        $this->assertSame('foo/file', $response->getHeaderLine('Content-Type'));
    }

    public function testStaticCreateFromResourceWithCustomContentType()
    {
        $stream = fopen("php://temp/maxmemory:1024", 'r+');

        $response = FileResponse::fromResource($stream)
            ->withHeader('content-type', 'foo/file');
        
        $this->assertSame('foo/file', $response->getHeaderLine('Content-Type'));
    }

    public function testStaticCreateFromResourceWithInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        FileResponse::fromResource('test');
    }

    public function testStaticCreateFromStreamWithCustomContentType()
    {
        $stream = HttpFactory::createStream('test');

        $response = FileResponse::fromStream($stream)
            ->withHeader('content-type', 'foo/file');
        
        $this->assertSame('foo/file', $response->getHeaderLine('Content-Type'));
    }

    public function testCanAddDownloadHeaders()
    {
        $stream = HttpFactory::createStream('test');

        $body = 'test';
        $status = 202;
        
        $response = (new FileResponse($stream))
            ->withStatus($status)
            ->withDownload('foo.txt')
            ->withHeader('x-foo', 'bar');
        
        $dispositionHeader = 'attachment; filename=foo.txt; filename*=UTF-8\'\'' . \rawurlencode('foo.txt');
        
        $this->assertSame('bar', $response->getHeaderLine('x-foo'));
        $this->assertSame('application/octet-stream', $response->getHeaderLine('content-type'));
        $this->assertSame($dispositionHeader, $response->getHeaderLine('content-disposition'));
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function invalidFileProvider()
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
     * @dataProvider invalidFileProvider
     */
    public function testRaisesExceptionforInvalidArguments($body)
    {
        $this->expectException(TypeError::class);
        new FileResponse($body);
    }

    public function testRaisesExceptionWhenStringIsNotAFilePath()
    {
        $this->expectException(InvalidArgumentException::class);
        new FileResponse('foo');
    }
}