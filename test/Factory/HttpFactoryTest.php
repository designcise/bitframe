<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Factory;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{StreamInterface, UploadedFileInterface};
use BitFrame\Factory\HttpFactory;
use BitFrame\Test\Asset\{HttpFactoryInterface, InteropMiddleware, PartialPsr17Factory};
use InvalidArgumentException;
use RuntimeException;

use function get_class;
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

    /**
     * @runInSeparateProcess
     */
    public function testCanAddValidFactory(): void
    {
        $factory = $this->getMockBuilder(HttpFactoryInterface::class)
            ->getMock();

        HttpFactory::addFactory($factory);

        self::assertSame($factory, HttpFactory::getFactory());
    }

    public function invalidFactoryProvider(): array
    {
        return [
            'random string' => ['randomString'],
            'invalid factory object' => [new InteropMiddleware()],
            'invalid factory class' => [InteropMiddleware::class],
            'implements some PSR-17 Factories' => [
                $this->getMockBuilder(PartialPsr17Factory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ],
        ];
    }

    /**
     * @dataProvider invalidFactoryProvider
     */
    public function testShouldNotAddInvalidFactory($factory): void
    {
        $this->expectException(InvalidArgumentException::class);

        HttpFactory::addFactory($factory);
    }

    /**
     * @runInSeparateProcess
     *
     * @throws RuntimeException
     */
    public function testNoFactoriesFound(): void
    {
        $reflection = new ReflectionClass(HttpFactory::class);
        $property = $reflection->getProperty('factoriesList');
        $property->setAccessible(true);
        $property->setValue([]);

        $this->expectException(RuntimeException::class);

        HttpFactory::getFactory();
    }

    /**
     * @runInSeparateProcess
     */
    public function testCanResolveCustomFactoryByClassName(): void
    {
        $customFactory = $this->getMockBuilder(HttpFactoryInterface::class)
            ->getMock();

        HttpFactory::addFactory($customFactory);

        self::assertInstanceOf(get_class($customFactory), HttpFactory::getFactory());
    }

    /**
     * @runInSeparateProcess
     *
     * @throws RuntimeException
     */
    public function testSkipsAndRemovesNonExistingFactory(): void
    {
        $reflection = new ReflectionClass(HttpFactory::class);
        $property = $reflection->getProperty('factoriesList');
        $property->setAccessible(true);
        $property->setValue([
            '\Non\Existent\Factory',
            ...$property->getValue($reflection),
        ]);

        HttpFactory::getFactory();

        $propertyAfter = $reflection->getProperty('factoriesList');
        $propertyAfter->setAccessible(true);
        $activeFactoriesList = $propertyAfter->getValue($reflection);

        self::assertNotContains('\Non\Existent\Factory', $activeFactoriesList);
    }

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

        self::assertSame($status, $response->getStatusCode());
        self::assertSame($phrase, $response->getReasonPhrase());
    }

    public function testCreateRequest(): void
    {
        $request = HttpFactory::createRequest('GET', '/');

        self::assertSame('GET', $request->getMethod());
        self::assertSame('/', (string) $request->getUri());
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

        self::assertSame($method, $request->getMethod());
        self::assertSame((string) $uri, (string) $request->getUri());
        self::assertSame($serverParams, $request->getServerParams());
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

        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            'scheme://host:81/path?query#fragment',
            (string) $request->getUri()
        );
        self::assertSame($parsedBody, $request->getParsedBody());
        self::assertSame($cookie, $request->getCookieParams());
        self::assertSame($serverParams, $request->getServerParams());
        self::assertSame(['foo' => 'bar'], $request->getParsedBody());
        self::assertSame($cookie, $request->getCookieParams());
        self::assertInstanceOf(UploadedFileInterface::class, $uploadedFiles['logo']);
        self::assertEquals('bitframe-logo.png', $uploadedFiles['logo']->getClientFilename());
        self::assertInstanceOf(StreamInterface::class, $reqBody);
        self::assertSame('hello world!', (string) $reqBody);
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

        self::assertSame($content, (string) $stream);
    }

    public function testCreateStreamFromFile(): void
    {
        $stream = HttpFactory::createStreamFromFile('php://temp', 'wb+');
        $stream->write('Foo bar!');

        self::assertSame('Foo bar!', (string) $stream);
    }

    public function testCreateStreamFromResource(): void
    {
        $resource = fopen('php://temp', 'rb+');
        fwrite($resource, 'Hello world');
        $stream = HttpFactory::createStreamFromResource($resource);

        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertTrue($stream->isWritable());
        self::assertTrue($stream->isSeekable());
        self::assertEquals('Hello world', (string) $stream);
    }

    public function testCreateUri(): void
    {
        $uri = HttpFactory::createUri('https://www.bitframe.com:8000/some/path');

        self::assertSame('www.bitframe.com', $uri->getHost());
        self::assertSame(8000, $uri->getPort());
        self::assertSame('/some/path', $uri->getPath());
        self::assertSame('https://www.bitframe.com:8000/some/path', (string) $uri);
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
