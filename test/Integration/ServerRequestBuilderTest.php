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
use BitFrame\Factory\HttpFactory;
use BitFrame\Http\ServerRequestBuilder;

/**
 * @covers \BitFrame\Http\ServerRequestBuilder
 */
class ServerRequestBuilderTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';

    /** @var object|\BitFrame\Factory\HttpFactoryInterface */
    private $factory;

    public function setUp(): void
    {
        $this->factory = HttpFactory::getFactory();
    }

    public function uriFromServerParamsProvider(): array
    {
        return [
            'Empty URI' => [[], '/'],
            'Only URI path defined with trailing slash' => [['REQUEST_URI' => '/',], '/'],
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
    public function testCanSetAllUriProperties(
        array $serverParams,
        string $expectedUri
    ): void {
        $serverRequest = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addUri()
            ->build();

        $this->assertSame($expectedUri, (string) $serverRequest->getUri());
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
     */
    public function testCanAddMethod(array $serverParams, string $expectedMethod): void
    {
        $serverRequest = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addMethod()
            ->build();

        $this->assertSame($expectedMethod, $serverRequest->getMethod());
    }
}
