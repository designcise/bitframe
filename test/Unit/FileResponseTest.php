<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Unit;

use PHPUnit\Framework\TestCase;
use BitFrame\Factory\HttpFactory;
use BitFrame\Http\Message\FileResponse;
use TypeError;
use InvalidArgumentException;

use function mime_content_type;
use function rawurlencode;
use function ctype_xdigit;
use function preg_match;

/**
 * @covers \BitFrame\Http\Message\FileResponse
 */
class FileResponseTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';
    
    public function testConstructorAcceptsStringFileName(): void
    {
        $file = self::ASSETS_DIR . 'test.txt';
        $mimeType = mime_content_type($file);
        $body = 'test';

        $response = new FileResponse($file);
        
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($mimeType, $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorAcceptsResource(): void
    {
        $stream = fopen('php://temp/maxmemory:1024', 'r+');
        fputs($stream, 'test');
        $body = 'test';

        $response = new FileResponse($stream);

        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            'application/octet-stream',
            $response->getHeaderLine('Content-Type')
        );
    }

    public function testConstructorAcceptsStreamInterfaceObject(): void
    {
        $stream = HttpFactory::createStream('test');
        $body = 'test';

        $response = new FileResponse($stream);

        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            'application/octet-stream',
            $response->getHeaderLine('Content-Type')
        );
    }

    public function testCanAddStatusAndHeader(): void
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

    public function testStaticCreateFromStringWithCustomContentType(): void
    {
        $response = FileResponse::fromPath(self::ASSETS_DIR . 'test.txt')
            ->withHeader('content-type', 'foo/file');
        
        $this->assertSame('foo/file', $response->getHeaderLine('Content-Type'));
    }

    public function testStaticCreateFromResourceWithCustomContentType(): void
    {
        $stream = fopen('php://temp/maxmemory:1024', 'r+');

        $response = FileResponse::fromResource($stream)
            ->withHeader('content-type', 'foo/file');
        
        $this->assertSame('foo/file', $response->getHeaderLine('Content-Type'));
    }

    public function testStaticCreateFromResourceWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FileResponse::fromResource('test');
    }

    public function testStaticCreateFromStreamWithCustomContentType(): void
    {
        $stream = HttpFactory::createStream('test');

        $response = FileResponse::fromStream($stream)
            ->withHeader('content-type', 'foo/file');
        
        $this->assertSame('foo/file', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @throws \Exception
     */
    public function testCanAddDownloadHeaders(): void
    {
        $stream = HttpFactory::createStream('test');

        $body = 'test';
        $status = 202;

        $response = (new FileResponse($stream))
            ->withStatus($status)
            ->withDownload('foo.txt')
            ->withHeader('x-foo', 'bar');

        $dispositionHeader = 'attachment; filename=foo.txt; filename*=UTF-8\'\'' . rawurlencode('foo.txt');
        
        $this->assertSame('bar', $response->getHeaderLine('x-foo'));
        $this->assertSame('application/octet-stream', $response->getHeaderLine('content-type'));
        $this->assertSame($dispositionHeader, $response->getHeaderLine('content-disposition'));
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    /**
     * @throws \Exception
     */
    public function testCanServeDownloadFileNameFromFileBaseName(): void
    {
        $response = (new FileResponse(self::ASSETS_DIR . 'test.txt'))
            ->withDownload();

        $dispositionHeader = 'attachment; filename=test.txt; filename*=UTF-8\'\'' . rawurlencode('test.txt');

        $this->assertSame($dispositionHeader, $response->getHeaderLine('content-disposition'));
    }

    /**
     * @throws \Exception
     */
    public function testCanAutoGenerateFileName(): void
    {
        $stream = HttpFactory::createStream('test');

        $response = (new FileResponse($stream))
            ->withDownload();

        $responseHeader = $response->getHeaderLine('content-disposition');
        preg_match('/filename=([^;]*)/', $responseHeader, $matches);

        $this->assertTrue($this->isRandomlyGeneratedFileName($matches[1]));
    }

    public function invalidFileProvider(): array
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
     *
     * @param mixed $body
     */
    public function testRaisesExceptionforInvalidArguments($body): void
    {
        $this->expectException(TypeError::class);
        new FileResponse($body);
    }

    public function testRaisesExceptionWhenStringIsNotAFilePath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FileResponse('foo');
    }

    /**
     * @param string $str
     *
     * @return bool
     */
    private function isRandomlyGeneratedFileName(string $str): bool
    {
        // is sha1 hash?
        return (ctype_xdigit($str) && (bool) preg_match('/^[0-9a-f]{40}$/i', $str));
    }
}
