<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Http;

use BitFrame\Http\Parser\UriParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BitFrame\Http\Parser\UriParser
 */
class UriParserTest extends TestCase
{
    public function uriFromServerParamsProvider(): array
    {
        return [
            'empty URI' => [[], '/', []],
            'only URI path defined with trailing slash' => [['REQUEST_URI' => '/',], '/', []],
            'URI (as delegated from sapi)' => [
                [
                    'REQUEST_SCHEME' => 'scheme',
                    'SERVER_NAME' => 'host',
                    'SERVER_PORT' => 81,
                    'REQUEST_URI' => '/path?key1=val1&key2=val2#fragment',
                ],
                'scheme://host:81/path?key1=val1&key2=val2#fragment',
                ['key1' => 'val1', 'key2' => 'val2'],
            ],
            'URI without scheme' => [
                [
                    'REQUEST_SCHEME' => null,
                    'SERVER_NAME' => 'host',
                    'SERVER_PORT' => 81,
                    'PATH_INFO' => 'path',
                    'QUERY_STRING' => 'key1=val1&key2=val2',
                ],
                'http://host:81/path?key1=val1&key2=val2',
                ['key1' => 'val1', 'key2' => 'val2'],
            ],
            'URI without host' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query#fragment',
                    'PATH_INFO' => 'path',
                    'ORIG_PATH_INFO' => 'orig-path',
                    'QUERY_STRING' => 'query',
                ],
                '/path?query#fragment',
                ['query' => ''],
            ],
            'URI without host using ORIG_PATH_INFO' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query#fragment',
                    'PATH_INFO' => '',
                    'ORIG_PATH_INFO' => 'orig-path',
                    'QUERY_STRING' => 'query',
                ],
                '/orig-path?query#fragment',
                ['query' => ''],
            ],
            'URI without host using path from REQUEST_URI' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query#fragment',
                    'PATH_INFO' => '',
                    'ORIG_PATH_INFO' => '',
                    'QUERY_STRING' => 'query-str',
                ],
                '/request-uri-path?query-str#fragment',
                ['query-str' => ''],
            ],
            'URI QUERY_STRING takes precedence' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query#fragment',
                    'QUERY_STRING' => 'query-str',
                ],
                '/request-uri-path?query-str#fragment',
                ['query-str' => ''],
            ],
            'URI REQUEST_URI query string when QUERY_STRING is empty' => [
                [
                    'REQUEST_URI' => '/request-uri-path?query',
                    'QUERY_STRING' => '',
                ],
                '/request-uri-path?query',
                ['query' => ''],
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
                ['query' => ''],
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
                ['query' => ''],
            ],
            'URI is not normalized' => [
                [
                    'REQUEST_SCHEME' => 'ScheMe',
                    'HTTP_HOST' => 'HoSt:81',
                    'REQUEST_URI' => '/path?query#fragment',
                ],
                'scheme://host:81/path?query#fragment',
                ['query' => ''],
            ],
            'URI with IPv4 host' => [
                [
                    'SERVER_ADDR' => '10.0.0.2',
                    'SERVER_PORT' => 3001,
                ],
                'http://10.0.0.2:3001',
                [],
            ],
            'URI with IPv4 host with trailing slash' => [
                [
                    'SERVER_ADDR' => '10.0.0.2/',
                    'SERVER_PORT' => 3001,
                ],
                'http://10.0.0.2:3001/',
                [],
            ],
            'URI with IPv6 host' => [
                [
                    'REQUEST_SCHEME' => 'scheme',
                    'SERVER_ADDR' => '[fe80:1234::%251]',
                    'SERVER_PORT' => 3001,
                ],
                'scheme://[fe80:1234::%251]:3001',
                [],
            ],
            'URI with IPv6 host with trailing slash' => [
                [
                    'REQUEST_SCHEME' => 'scheme',
                    'SERVER_ADDR' => '[fe80:1234::%251]/',
                    'SERVER_PORT' => 3001,
                ],
                'scheme://[fe80:1234::%251]:3001/',
                [],
            ],
        ];
    }

    /**
     * @dataProvider uriFromServerParamsProvider
     *
     * @param array $serverParams
     * @param string $expectedUri
     * @param array $expectedQueryParams
     */
    public function testCanAddAndGetUriFromServerParams(
        array $serverParams,
        string $expectedUri,
        array $expectedQueryParams,
    ): void {
        [$uri, $queryParams] = UriParser::parse($serverParams);

        $this->assertSame($expectedUri, $uri);
        $this->assertSame($expectedQueryParams, $queryParams);
    }
}
