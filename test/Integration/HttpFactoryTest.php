<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{StreamInterface, UploadedFileInterface};
use BitFrame\Factory\HttpFactory;

use function fopen;
use function fwrite;

use const UPLOAD_ERR_OK;

/**
 * @covers \BitFrame\Factory\HttpFactory
 */
class HttpFactoryTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';

    public function responseArgsProvider(): array
    {
        return [
            'status with no phrase' => [200, ''],
            'status with phrase' => [404, 'uh oh!'],
        ];
    }

    /**
     * @dataProvider responseArgsProvider
     */
    public function testCreateResponse(int $status, string $phrase): void
    {
        $response = HttpFactory::createResponse($status, $phrase);

        $this->assertSame($status, $response->getStatusCode());
        $this->assertSame($phrase, $response->getReasonPhrase());
    }

    public function testCreateRequest(): void
    {
        $request = HttpFactory::createRequest('GET', '/');

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/', (string) $request->getUri());
    }

    public function serverRequestArgsProvder(): array
    {
        return [
            'post with empty uri and server params' => ['POST', '', []],
            'server params should not affect uri' => ['PUT', '/hello', [
                'REQUEST_SCHEME' => 'scheme',
                'SERVER_NAME' => 'host',
                'SERVER_PORT' => 81,
                'REQUEST_URI' => '/path?query#fragment',
            ]],
            'accepts UriInterface object' => [
                'GET',
                HttpFactory::createUri('/test'),
                []
            ],
        ];
    }

    /**
     * @dataProvider serverRequestArgsProvder
     *
     * @param string $method
     * @param $uri
     * @param array $serverParams
     */
    public function testCreateServerRequest(
        string $method,
        $uri,
        array $serverParams
    ): void {
        $request = HttpFactory::createServerRequest($method, $uri, $serverParams);

        $this->assertSame($method, $request->getMethod());
        $this->assertSame((string) $uri, (string) $request->getUri());
        $this->assertSame($serverParams, $request->getServerParams());
    }

    public function testCreateServerRequestFromGlobals(): void {
        $serverParams = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_SCHEME' => 'scheme',
            'SERVER_NAME' => 'host',
            'SERVER_PORT' => 81,
            'REQUEST_URI' => '/path?query#fragment',
        ];
        $parsedBody = ['foo' => 'bar'];
        $cookie = ['baz' => 'qux'];
        $files = [
            'logo' => [
                'tmp_name' => self::ASSETS_DIR . 'logo.png',
                'name' => 'bitframe-logo.png',
                'size' => 8316,
                'type' => 'image/png',
                'error' => 0,
            ],
        ];

        $request = HttpFactory::createServerRequestFromGlobals(
            $serverParams,
            $parsedBody,
            $cookie,
            $files,
            'hello world!'
        );

        $uploadedFiles = $request->getUploadedFiles();
        $reqBody = $request->getBody();

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(
            'scheme://host:81/path?query#fragment',
            (string) $request->getUri()
        );
        $this->assertSame($parsedBody, $request->getParsedBody());
        $this->assertSame($cookie, $request->getCookieParams());
        $this->assertSame($serverParams, $request->getServerParams());
        $this->assertSame(['foo' => 'bar'], $request->getParsedBody());
        $this->assertSame($cookie, $request->getCookieParams());
        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFiles['logo']);
        $this->assertEquals('bitframe-logo.png', $uploadedFiles['logo']->getClientFilename());
        $this->assertInstanceOf(StreamInterface::class, $reqBody);
        $this->assertSame('hello world!', (string) $reqBody);
    }

    public function createStreamArgsProvider(): array
    {
        return [
            'empty string' => [''],
            'random string' => ['hello world'],
        ];
    }

    /**
     * @dataProvider createStreamArgsProvider
     *
     * @param string $content
     */
    public function testCreateStream(string $content): void
    {
        $stream = HttpFactory::createStream($content);

        $this->assertSame($content, (string) $stream);
    }

    public function testCreateStreamFromFile(): void
    {
        $stream = HttpFactory::createStreamFromFile('php://temp', 'wb+');
        $stream->write('Foo bar!');

        $this->assertSame('Foo bar!', (string) $stream);
    }

    public function testCreateStreamFromResource(): void
    {
        $resource = fopen('php://temp', 'rb+');
        fwrite($resource, 'Hello world');
        $stream = HttpFactory::createStreamFromResource($resource);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('Hello world', (string) $stream);
    }

    public function testCreateUri(): void
    {
        $uri = HttpFactory::createUri('https://www.bitframe.com:8000/some/path');

        $this->assertSame('www.bitframe.com', $uri->getHost());
        $this->assertSame(8000, $uri->getPort());
        $this->assertSame('/some/path', $uri->getPath());
        $this->assertSame('https://www.bitframe.com:8000/some/path', (string) $uri);
    }

    public function testUploadedFile(): void
    {
        $stream = HttpFactory::createStream('php://temp');
        $file = HttpFactory::createUploadedFile($stream, 123, UPLOAD_ERR_OK, 'foobar.baz', 'mediatype');

        $this->assertSame(123, $file->getSize());
        $this->assertSame('foobar.baz', $file->getClientFilename());
        $this->assertSame('mediatype', $file->getClientMediaType());
    }
}
