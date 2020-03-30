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
use Psr\Http\Message\{ServerRequestInterface, UploadedFileInterface};
use BitFrame\Factory\HttpFactory;
use BitFrame\Http\ServerRequestBuilder;
use BitFrame\Parser\JsonMediaParser;

/**
 * @covers \BitFrame\Http\ServerRequestBuilder
 */
class ServerRequestBuilderTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';

    /** @var object|HttpFactoryInterface */
    private $factory;

    public function setUp(): void
    {
        $this->factory = HttpFactory::getFactory();
    }

    public function uriFromServerParamsProvider()
    {
        return [
            'Empty URI' => [
                [], 
                '/'
            ],
            'Only URI path defined with trailing slash' => [
                [
                    'REQUEST_URI' => '/',
                ],
                '/'
            ],

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
                    'PATH_INFO' => 'path',
                    'QUERY_STRING' => 'query',
                ],
                '/path?query',
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
     * @param array $serverParams
     * @param string $expectedUri
     */
    public function testCanSetAllUriProperties(array $serverParams, string $expectedUri)
    {
        $serverRequest = (new ServerRequestBuilder($serverParams, $this->factory))
            ->addUri()
            ->build();

        $this->assertSame($expectedUri, (string) $serverRequest->getUri());
    }
}