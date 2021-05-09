<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Http;

use BitFrame\Test\Asset\PartialPsr17Factory;
use SimpleXMLElement;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    StreamInterface,
    UploadedFileInterface
};
use BitFrame\Test\Asset\InteropMiddleware;
use BitFrame\Factory\HttpFactory;
use BitFrame\Http\ServerRequestBuilder;
use TypeError;
use UnexpectedValueException;
use InvalidArgumentException;

use function fopen;
use function fwrite;

/**
 * @covers \BitFrame\Http\ServerRequestBuilder
 */
class ServerRequestBuilderTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';

    /** @var ServerRequestFactoryInterface|StreamFactoryInterface|UploadedFileFactoryInterface */
    private $factory;

    public function setUp(): void
    {
        $this->factory = HttpFactory::getFactory();
    }

    public function invalidFactoryProvider(): array
    {
        return [
            'invalid factory object' => [new InteropMiddleware()],
            'implements some PSR-17 Factories' => [
                $this->getMockBuilder(PartialPsr17Factory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ],
        ];
    }

    /**
     * @dataProvider invalidFactoryProvider
     *
     * @param ServerRequestFactoryInterface|StreamFactoryInterface $factory
     */
    public function testShouldThrowExceptionWhenFactoryIsInvalid($factory): void {
        $this->expectException(TypeError::class);

        new ServerRequestBuilder([], $factory);
    }

    public function uriFromServerParamsProvider(): array
    {
        return [
            'empty URI' => [[], '/'],
            'only URI path defined with trailing slash' => [['REQUEST_URI' => '/',], '/'],
            'URI (as delegated from sapi)' => [
                [
                    'REQUEST_SCHEME' => 'scheme',
                    'SERVER_NAME' => 'host',
                    'SERVER_PORT' => 81,
                    'REQUEST_URI' => '/path?query#fragment',
                ],
                'scheme://host:81/path?query#fragment',
            ],
            'URI without scheme' => [
                [
                    'REQUEST_SCHEME' => null,
                    'SERVER_NAME' => 'host',
                    'SERVER_PORT' => 81,
                    'PATH_INFO' => 'path',
                    'QUERY_STRING' => 'query',
                ],
                'http://host:81/path?query',
            ],
            'URI without host' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query#fragment',
                    'PATH_INFO' => 'path',
                    'ORIG_PATH_INFO' => 'orig-path',
                    'QUERY_STRING' => 'query',
                ],
                '/path?query#fragment',
            ],
            'URI without host using ORIG_PATH_INFO' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query#fragment',
                    'PATH_INFO' => '',
                    'ORIG_PATH_INFO' => 'orig-path',
                    'QUERY_STRING' => 'query',
                ],
                '/orig-path?query#fragment',
            ],
            'URI without host using path from REQUEST_URI' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query#fragment',
                    'PATH_INFO' => '',
                    'ORIG_PATH_INFO' => '',
                    'QUERY_STRING' => 'query-str',
                ],
                '/request-uri-path?query-str#fragment',
            ],
            'URI QUERY_STRING takes precedence' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query#fragment',
                    'QUERY_STRING' => 'query-str',
                ],
                '/request-uri-path?query-str#fragment',
            ],
            'URI REQUEST_URI query string when QUERY_STRING is empty' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query',
                    'QUERY_STRING' => '',
                ],
                '/request-uri-path?query',
            ],
            'URI with https' => [
                [
                    'HTTPS' => 'on',
                    'SERVER_NAME' => 'host',
                    'SERVER_PORT' => 81,
                    'PATH_INFO' => 'path',
                    'QUERY_STRING' => 'query',
                ],
                'https://host:81/path?query',
            ],
            'REQUEST_SCHEME takes precedence over HTTPS' => [
                [
                    'HTTPS' => 'on',
                    'REQUEST_SCHEME' => 'http',
                    'SERVER_NAME' => 'host',
                    'SERVER_PORT' => 81,
                    'PATH_INFO' => 'path',
                    'QUERY_STRING' => 'query',
                ],
                'http://host:81/path?query',
            ],
            'URI with empty port' => [
                [
                    'HTTP_HOST' => 'host:',
                    'REQUEST_URI' => '/path?query#fragment',
                ],
                'http://host/path?query#fragment',
            ],
            'URI is not normalized' => [
                [
                    'REQUEST_SCHEME' => 'ScheMe',
                    'HTTP_HOST' => 'HoSt:81',
                    'REQUEST_URI' => '/path?query#fragment',
                ],
                'scheme://host:81/path?query#fragment',
            ],
            'URI with IPv4 host' => [
                [
                    'SERVER_ADDR' => '10.0.0.2',
                    'SERVER_PORT' => 3001,
                ],
                'http://10.0.0.2:3001',
            ],
            'URI with IPv4 host with trailing slash' => [
                [
                    'SERVER_ADDR' => '10.0.0.2/',
                    'SERVER_PORT' => 3001,
                ],
                'http://10.0.0.2:3001/',
            ],
            'URI with IPv6 host' => [
                [
                    'REQUEST_SCHEME' => 'scheme',
                    'SERVER_ADDR' => '[fe80:1234::%251]',
                    'SERVER_PORT' => 3001,
                ],
                'scheme://[fe80:1234::%251]:3001',
            ],
            'URI with IPv6 host with trailing slash' => [
                [
                    'REQUEST_SCHEME' => 'scheme',
                    'SERVER_ADDR' => '[fe80:1234::%251]/',
                    'SERVER_PORT' => 3001,
                ],
                'scheme://[fe80:1234::%251]:3001/',
            ],
        ];
    }

    /**
     * @dataProvider uriFromServerParamsProvider
     *
     * @param array $serverParams
     * @param string $expectedUri
     */
    public function testCanAddAndGetUriFromServerParams(
        array $serverParams,
        string $expectedUri
    ): void {
        $serverRequest = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addUri()
            ->build();

        $this->assertSame($expectedUri, (string) $serverRequest->getUri());
    }

    public function queryParamsProvider(): array
    {
        return [
            'empty' => [
                [
                    'REQUEST_URI' => '/path?#fragment'
                ],
                []
            ],
            'empty value' => [
                [
                    'REQUEST_URI' => '/path?key=#fragment',
                ],
                ['key' => '']
            ],
            'from REQUEST_URI' => [
                [
                    'REQUEST_URI' => '/path?foo=bar&baz=qux#fragment',
                ],
                ['foo' => 'bar', 'baz' => 'qux'],
            ],
            'from QUERY_STRING' => [
                [
                    'QUERY_STRING' => 'foo=bar&callback=hello',
                ],
                ['foo' => 'bar', 'callback' => 'hello']
            ],
            'encoded query string in REQUEST_URI' => [
                [
                    'REQUEST_URI' => '/path?url=https%3A%2F%2Fbitframephp.com%2F',
                ],
                ['url' => 'https://bitframephp.com/']
            ],
            'encoded query string in QUERY_STRING' => [
                [
                    'QUERY_STRING' => 'url=https%3A%2F%2Fbitframephp.com%2F',
                ],
                ['url' => 'https://bitframephp.com/']
            ],
        ];
    }

    /**
     * @dataProvider queryParamsProvider
     *
     * @param array $serverParams
     * @param array $expected
     */
    public function testCanGetQueryParamsFromServerParams(
        array $serverParams,
        array $expected
    ): void {
        $serverRequest = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addUri()
            ->build();

        $this->assertSame($expected, $serverRequest->getQueryParams());
    }

    public function testDefaultBuildValues(): void
    {
        $serverRequest = (new ServerRequestBuilder([], $this->factory))
            ->build();

        $this->assertSame('GET', $serverRequest->getMethod());
        $this->assertSame('/', (string) $serverRequest->getUri());
        $this->assertSame('1.1', $serverRequest->getProtocolVersion());
        $this->assertSame([], $serverRequest->getHeaders());
        $this->assertSame([], $serverRequest->getCookieParams());
        $this->assertNull($serverRequest->getParsedBody());
        $this->assertSame('', (string) $serverRequest->getBody());
    }

    public function testFromSapiWithEmptyArray(): void
    {
        $serverRequest = ServerRequestBuilder::fromSapi([], $this->factory);

        $this->assertSame('GET', $serverRequest->getMethod());
        $this->assertSame('/', (string) $serverRequest->getUri());
        $this->assertSame('1.1', $serverRequest->getProtocolVersion());
        $this->assertSame([], $serverRequest->getHeaders());
        $this->assertSame([], $serverRequest->getCookieParams());
        $this->assertNull($serverRequest->getParsedBody());
        $this->assertSame('', (string) $serverRequest->getBody());
    }

    public function methodProvider(): array
    {
        return [
            'no request method' => [[], 'GET'],
            'null' => [['REQUEST_METHOD' => null], 'GET'],
            'empty string' => [['REQUEST_METHOD' => ''], 'GET'],
            'empty array' => [['REQUEST_METHOD' => []], 'GET'],
            'boolean false' => [['REQUEST_METHOD' => false], 'GET'],
            'number 0' => [['REQUEST_METHOD' => 0], 'GET'],
            'float 0.0' => [['REQUEST_METHOD' => 0.0], 'GET'],
            'string 0' => [['REQUEST_METHOD' => '0'], 'GET'],
            'GET request method' => [['REQUEST_METHOD' => 'GET'], 'GET'],
            'POST request method' => [['REQUEST_METHOD' => 'POST'], 'POST'],
            'PUT request method' => [['REQUEST_METHOD' => 'PUT'], 'PUT'],
            'PATCH request method' => [['REQUEST_METHOD' => 'PATCH'], 'PATCH'],
            'DELETE request method' => [['REQUEST_METHOD' => 'DELETE'], 'DELETE'],
            'HEAD request method' => [['REQUEST_METHOD' => 'HEAD'], 'HEAD'],
            'OPTIONS request method' => [['REQUEST_METHOD' => 'OPTIONS'], 'OPTIONS'],
            'lowercase GET request method' => [['REQUEST_METHOD' => 'get'], 'get'],
            'non-standard request method' => [['REQUEST_METHOD' => 'TEST'], 'TEST'],
            'non-standard mixed case method' => [['REQUEST_METHOD' => 'tEsT'], 'tEsT'],
        ];
    }

    /**
     * @dataProvider methodProvider
     *
     * @param array $serverParams
     * @param string $expectedMethod
     */
    public function testCanAddMethod(array $serverParams, string $expectedMethod): void
    {
        $serverRequest = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addMethod()
            ->build();

        $this->assertSame($expectedMethod, $serverRequest->getMethod());
    }

    public function cookiesProvider(): array
    {
        return [
            'no cookies' => [[], [], []],
            'empty HTTP_COOKIE' => [['HTTP_COOKIE' => ''], [], []],
            'HTTP_COOKIE array' => [['HTTP_COOKIE' => 'foo_bar=baz'], [], ['foo_bar' => 'baz']],
            'url encoded HTTP_COOKIE string' => [['HTTP_COOKIE' => 'Set-Cookie%3A%20test%3D1234;'], [], []],
            'HTTP_COOKIE string' => [
                [
                    'HTTP_COOKIE' => 'Set-Cookie: foo=bar; domain=test.com; path=/; expires=Wed, 30 Aug 2019 00:00:00 GMT',
                ],
                [],
                ['foo' => 'bar', 'domain' => 'test.com', 'path' => '/']
            ],
            'parsed cookies' => [
                [],
                ['foo' => 'bar'],
                ['foo' => 'bar']
            ],
            'parsed cookies take precedence over HTTP_COOKIE' => [
                [
                    'HTTP_COOKIE' => 'Set-Cookie: foo=bar; domain=test.com; path=/; expires=Wed, 30 Aug 2019 00:00:00 GMT',
                ],
                ['hello' => 'world'],
                ['hello' => 'world']
            ],
            'ows without fold' => [
                ['HTTP_COOKIE' => "\tfoo=bar "],
                [],
                ['foo' => 'bar'],
            ],
            'url encoded value' => [
                ['HTTP_COOKIE' => 'foo=bar%3B+'],
                [],
                ['foo' => 'bar; '],
            ],
            'double quoted value' => [
                ['HTTP_COOKIE' => 'foo="bar"'],
                [],
                ['foo' => 'bar'],
            ],
            'multiple pairs' => [
                ['HTTP_COOKIE' => 'foo=bar; baz="bat"; bau=bai'],
                [],
                ['foo' => 'bar', 'baz' => 'bat', 'bau' => 'bai'],
            ],
            'same-name pairs' => [
                ['HTTP_COOKIE' => 'foo=bar; foo="bat"'],
                [],
                ['foo' => 'bat'],
            ],
            'period in name' => [
                ['HTTP_COOKIE' => 'foo.bar=baz'],
                [],
                ['foo.bar' => 'baz'],
            ],
        ];
    }

    /**
     * @dataProvider cookiesProvider
     *
     * @param array $serverParams
     * @param array $cookieParams
     * @param array $expected
     */
    public function testCanAddCookieParams(
        array $serverParams,
        array $cookieParams,
        array $expected
    ): void {
        $serverRequest = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addCookieParams($cookieParams)
            ->build();

        $this->assertSame($expected, $serverRequest->getCookieParams());
    }

    public function bodyProvider(): array
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'Hello world');

        return [
            'null' => [null, ''],
            'empty string' => ['', ''],
            'empty array' => [[], ''],
            'boolean false' => [false, ''],
            'boolean true' => [true, '1'],
            'number 0' => [0, '0'],
            'float 0.0' => [0.0, '0'],
            'string 0' => ['0', '0'],
            'array is ignored' => [['foo', 'bar'], ''],
            'object is ignored' => [(object) ['foo', 'bar'], ''],
            'accepts string' => ['hello world', 'hello world'],
            'accepts number' => [123456789, '123456789'],
            'accepts float' => [1.25, '1.25'],
            'accepts numeric string' => ['0123456789', '0123456789'],
            'accepts boolean true as string' => ['true', 'true'],
            'accepts boolean false as string' => ['false', 'false'],
            'accepts resource' => [$resource, 'Hello world'],
            'accepts StreamInterface' => [HttpFactory::createStream('Foo'), 'Foo'],
        ];
    }

    /**
     * @dataProvider bodyProvider
     *
     * @param resource|string|StreamInterface $body
     * @param string $expected
     */
    public function testCanAddBody(
        $body,
        string $expected
    ): void {
        $serverRequest = (new ServerRequestBuilder([], $this->factory))
            ->addBody($body)
            ->build();

        $requestBody = $serverRequest->getBody();

        $this->assertInstanceOf(StreamInterface::class, $requestBody);
        $this->assertSame($expected, (string) $requestBody);
    }

    public function invalidParsedBodyProvider(): array
    {
        return [
            'string' => ['foobar'],
            'float' => [1.5],
            'int' => [1234],
            'resource' => [fopen('php://temp', 'rb+')],
            'boolean' => [true],
        ];
    }

    /**
     * @dataProvider invalidParsedBodyProvider
     *
     * @param mixed $parsedBody
     */
    public function testCantAddInvalidParsedBody(mixed $parsedBody): void
    {
        $this->expectException(TypeError::class);

        (new ServerRequestBuilder([], $this->factory))
            ->addParsedBody($parsedBody)
            ->build();
    }

    public function validParsedBodyProvider(): array
    {
        return [
            'null' => [null],
            'empty array' => [[]],
            'array' => [['foo' => 'bar']],
            'object' => [new SimpleXMLElement('<data value="tada">Test</data>')],
        ];
    }

    /**
     * @dataProvider validParsedBodyProvider
     *
     * @param null|array|object $parsedBody
     */
    public function testCanAddParsedBody($parsedBody): void
    {
        $request = (new ServerRequestBuilder([], $this->factory))
            ->addParsedBody($parsedBody)
            ->build();

        $this->assertSame($parsedBody, $request->getParsedBody());
    }

    public function parseBodyProvider(): array
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'Hello world');

        return [
            'string' => ['hello world', ['hello_world' => '']],
            'query string' => ['foo=bar&baz=qux', ['foo' => 'bar', 'baz' => 'qux']],
            'query string name mangling' => ['Foo bar=baz qux', ['Foo_bar' => 'baz qux']],
            'number' => [123456789, ['123456789' => '']],
            'float' => [1.25, ['1_25' => '']],
            'numeric string' => ['0123456789', ['0123456789' => '']],
            'boolean true as string' => ['true', ['true' => '']],
            'boolean false as string' => ['false', ['false' => '']],
            'resource' => [$resource, ['Hello_world' => '']],
            'StreamInterface' => [HttpFactory::createStream('Foo'), ['Foo' => '']],
            'StreamInterface query string' => [
                HttpFactory::createStream('foo=bar&baz=qux'),
                ['foo' => 'bar', 'baz' => 'qux']
            ],
            'StreamInterface query string name mangling' => [
                HttpFactory::createStream('Foo bar=baz qux'),
                ['Foo_bar' => 'baz qux']
            ],
            'empty json' => ['{}', [], 'application/json'],
            'basic json' => [
                '{"name":"John", "age":30, "car":null}', [
                    'name' => 'John',
                    'age' => 30,
                    'car' => null,
                ],
                'application/json'
            ],
            'json array' => [
                '{"name":"John", "age":30, "cars":[ "Ford", "BMW", "Fiat" ]}', [
                    'name' => 'John',
                    'age' => 30,
                    'cars' => ['Ford', 'BMW', 'Fiat'],
                ],
                'application/json'
            ],
            'empty key' => [
                '{ "": { "foo": "" } }',
                ['' => ['foo' => '']],
                'application/json'
            ],
            'empty key value' => [
                '{ "": { "": "" } }',
                ['' => ['' => '']],
                'application/json'
            ],
        ];
    }

    /**
     * @dataProvider parseBodyProvider
     *
     * @param resource|string|StreamInterface $body
     * @param array $expected
     * @param string $mimeType
     */
    public function testShouldParseBodyWhenParsedBodyIsEmpty(
        $body,
        $expected,
        string $mimeType = 'text/plain'
    ): void {
        $request = (new ServerRequestBuilder(['HTTP_ACCEPT' => $mimeType], $this->factory))
            ->addHeaders()
            ->addBody($body)
            ->build();

        $this->assertSame($expected, $request->getParsedBody());
    }

    public function validProtocolVersionProvider(): array
    {
        return [
            'default value when not set' => [[], '1.1'],
            'int' => [['SERVER_PROTOCOL' => 1], '1'],
            'string int' => [['SERVER_PROTOCOL' => '1'], '1'],
            'float' => [['SERVER_PROTOCOL' => 1.5], '1.5'],
            'string float' => [['SERVER_PROTOCOL' => '2.0'], '2.0'],
            'standard protocol syntax' => [['SERVER_PROTOCOL' => 'HTTP/2.0'], '2.0'],
            'HTTP/1.0' => [['SERVER_PROTOCOL' => 'HTTP/1.0'], '1.0'],
            'HTTP/1.1' => [['SERVER_PROTOCOL' => 'HTTP/1.1'], '1.1'],
            'HTTP/2' => [['SERVER_PROTOCOL' => 'HTTP/2'], '2'],
        ];
    }

    /**
     * @dataProvider validProtocolVersionProvider
     *
     * @param array $serverParams
     * @param string $expected
     */
    public function testCanAddProtocolVersion(
        array $serverParams,
        string $expected
    ): void {
        $serverRequest = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addProtocolVersion()
            ->build();

        $this->assertSame($expected, $serverRequest->getProtocolVersion());
    }

    public function invalidProtocolVersionProvider(): array
    {
        return [
            'invalid protocol' => ['INVALID/2.0'],
            'letters only' => ['abc'],
            'alphanumeric' => ['abc123'],
            'lowercase' => ['http/2.0'],
            'mixed case' => ['hTtP/2.0'],
        ];
    }

    /**
     * @dataProvider invalidProtocolVersionProvider
     *
     * @param string $protocol
     */
    public function testInvalidProtocolVersionShouldThrowException(string $protocol): void
    {
        $this->expectException(UnexpectedValueException::class);

        (new ServerRequestBuilder(['SERVER_PROTOCOL' => $protocol], $this->factory))
            ->addProtocolVersion()
            ->build();
    }

    public function testCanAddUploadedFiles(): void
    {
        $stream = HttpFactory::createStreamFromFile('php://temp');
        $files = [
                'files' => [
                'tmp_name' => $stream,
                'size' => 0,
                'error' => 0,
                'name' => 'foo.bar',
                'type' => 'text/plain',
            ]
        ];

        $request = (new ServerRequestBuilder([], $this->factory))
            ->addUploadedFiles($files)
            ->build();

        $expectedFiles = [
            'files' => HttpFactory::createUploadedFile($stream, 0, 0, 'foo.bar', 'text/plain')
        ];

        $this->assertEquals($expectedFiles, $request->getUploadedFiles());
    }

    public function testAddUploadedFileFromFileSpecification(): void
    {
        $files = [
            'logo' => [
                'tmp_name' => self::ASSETS_DIR . 'logo.png',
                'name' => 'bitframe-logo.png',
                'size' => 8316,
                'type' => 'image/png',
                'error' => 0,
            ],
        ];

        $request = (new ServerRequestBuilder([], $this->factory))
            ->addUploadedFiles($files)
            ->build();

        $normalized = $request->getUploadedFiles();

        $this->assertCount(1, $normalized);
        $this->assertInstanceOf(UploadedFileInterface::class, $normalized['logo']);
        $this->assertEquals('bitframe-logo.png', $normalized['logo']->getClientFilename());
    }

    public function testTraversesNestedFileSpecificationToExtractUploadedFile(): void
    {
        $files = [
            'my-form' => [
                'details' => [
                    'logo' => [
                        'tmp_name' => self::ASSETS_DIR . 'logo.png',
                        'name' => 'bitframe-logo.png',
                        'size' => 8316,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
            ],
        ];

        $request = (new ServerRequestBuilder([], $this->factory))
            ->addUploadedFiles($files)
            ->build();

        $normalized = $request->getUploadedFiles();

        $this->assertCount(1, $normalized);
        $this->assertEquals('bitframe-logo.png', $normalized['my-form']['details']['logo']->getClientFilename());
    }

    public function testTraversesNestedFileSpecificationContainingNumericIndicesToExtractUploadedFiles(): void
    {
        $files = [
            'my-form' => [
                'details' => [
                    'avatars' => [
                        'tmp_name' => [
                            0 => self::ASSETS_DIR . 'logo.png',
                            1 => self::ASSETS_DIR . 'logo-1.png',
                            2 => self::ASSETS_DIR . 'logo-2.png',
                        ],
                        'name' => [
                            0 => 'file1.txt',
                            1 => 'file2.txt',
                            2 => 'file3.txt',
                        ],
                        'size' => [
                            0 => 100,
                            1 => 240,
                            2 => 750,
                        ],
                        'type' => [
                            0 => 'plain/txt',
                            1 => 'image/jpg',
                            2 => 'image/png',
                        ],
                        'error' => [
                            0 => 0,
                            1 => 0,
                            2 => 0,
                        ],
                    ],
                ],
            ],
        ];

        $request = (new ServerRequestBuilder([], $this->factory))
            ->addUploadedFiles($files)
            ->build();

        $normalized = $request->getUploadedFiles();

        $this->assertCount(3, $normalized['my-form']['details']['avatars']);
        $this->assertEquals('file1.txt', $normalized['my-form']['details']['avatars'][0]->getClientFilename());
        $this->assertEquals('file2.txt', $normalized['my-form']['details']['avatars'][1]->getClientFilename());
        $this->assertEquals('file3.txt', $normalized['my-form']['details']['avatars'][2]->getClientFilename());
    }

    public function testInvalidNestedFileSpecShouldThrowException(): void
    {
        $files = [
            'test' => false,
        ];

        $this->expectException(InvalidArgumentException::class);

        (new ServerRequestBuilder([], $this->factory))
            ->addUploadedFiles($files)
            ->build();
    }

    public function testEmptyFileSpec(): void
    {
        $files = [];

        $request = (new ServerRequestBuilder([], $this->factory))
            ->addUploadedFiles($files)
            ->build();

        $this->assertSame([], $request->getUploadedFiles());
    }

    public function testCanAddAlreadyNormalizedUploadedFileSpec(): void
    {
        $stream = HttpFactory::createStreamFromFile('php://temp');
        $uploadedFile = HttpFactory::createUploadedFile($stream, 0, 0, 'foo.bar', 'text/plain');

        $files = [
            'foo_bar' => $uploadedFile,
        ];

        $request = (new ServerRequestBuilder([], $this->factory))
            ->addUploadedFiles($files)
            ->build();

        $normalized = $request->getUploadedFiles();

        $this->assertCount(1, $normalized);
        $this->assertInstanceOf(UploadedFileInterface::class, $normalized['foo_bar']);
    }

    public function testCanMarshalHeadersPrefixedByApache(): void
    {
        $serverParams = [
            'HTTP_X_FOO_BAR' => 'nonprefixed',
            'REDIRECT_HTTP_AUTHORIZATION' => 'token',
            'REDIRECT_HTTP_X_FOO_BAR' => 'prefixed',
        ];
        $expected = [
            'authorization' => ['token'],
            'x-foo-bar' => ['nonprefixed'],
        ];

        $request = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addHeaders()
            ->build();

        $this->assertEquals($expected, $request->getHeaders());
    }

    public function testInvalidHeadersAreStripped(): void
    {
        $serverParams = [
            'COOKIE' => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'MD5' => 'CONTENT-MD5',
            'CONTENT_LENGTH' => 'UNSPECIFIED',
        ];

        // headers that don't begin with `HTTP_` or `CONTENT_` will not be returned
        $expected = [
            'authorization' => ['token'],
            'content-length' => ['UNSPECIFIED'],
        ];

        $request = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addHeaders()
            ->build();

        $this->assertEquals($expected, $request->getHeaders());
    }

    public function testMarshalsExpectedHeaders(): void
    {
        $serverParams = [
            'HTTP_COOKIE' => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_FOO_BAR' => 'FOOBAR',
            'CONTENT_MD5' => 'CONTENT-MD5',
            'CONTENT_LENGTH' => 'UNSPECIFIED',
        ];

        $expected = [
            'cookie' => ['COOKIE'],
            'authorization' => ['token'],
            'content-type' => ['application/json'],
            'accept' => ['application/json'],
            'x-foo-bar' => ['FOOBAR'],
            'content-md5' => ['CONTENT-MD5'],
            'content-length' => ['UNSPECIFIED'],
        ];

        $request = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addHeaders()
            ->build();

        $this->assertEquals($expected, $request->getHeaders());
    }
}
