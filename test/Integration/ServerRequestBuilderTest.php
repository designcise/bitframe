<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
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
            /* 
            'URI from server params #1' => [
                [
                    'HTTPS' => 'on',
                    'HTTP_HOST' => 'host:81',
                    'REQUEST_URI' => '/path?query#fragment',
                ],
                'https://host:81/path?query#fragment',
            ],
            'URI from server params #2' => [
                [
                    'REQUEST_SCHEME' => 'scheme',
                    'SERVER_ADDR' => '10.0.0.2',
                    'PATH_INFO' => 'path',
                    'QUERY_STRING' => 'key1=value1&key2=value2',
                ],
                'scheme://10.0.0.2/path?key1=value1&key2=value2',
            ],
            'URI without authority and scheme' => [
                [
                    'REQUEST_URI' => '/',
                ],
                '/',
            ],
            'URI with empty host' => [
                [
                    'SERVER_NAME' => '',
                    'PATH_INFO' => '/path',
                    'QUERY_STRING' => 'query',
                ],
                'scheme:///path?query',
            ], */
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

    /* public function testDefaultBuildValues()
    {
        $serverRequest = (new ServerRequestBuilder([], $this->factory))
            ->build();
        
        $this->assertSame('GET', $serverRequest->getMethod());
        $this->assertSame('/', (string) $serverRequest->getUri());
        $this->assertSame('1.1', $serverRequest->getProtocolVersion());
        $this->assertSame([], $serverRequest->getHeaders());
        $this->assertSame([], $serverRequest->getCookieParams());
        $this->assertSame(null, $serverRequest->getParsedBody());
        $this->assertSame('', (string) $serverRequest->getBody());
    }

    public function testStaticFromSapiWithEmptyArray()
    {
        $serverRequest = ServerRequestBuilder::fromSapi([], $this->factory);
        
        $this->assertSame('GET', $serverRequest->getMethod());
        $this->assertSame('/', (string) $serverRequest->getUri());
        $this->assertSame('1.1', $serverRequest->getProtocolVersion());
        $this->assertSame([], $serverRequest->getHeaders());
        $this->assertSame([], $serverRequest->getCookieParams());
        $this->assertSame(null, $serverRequest->getParsedBody());
        $this->assertSame('', (string) $serverRequest->getBody());
    }

    public function testCanAddMethod()
    {
        $server = [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/67.0.3396.99 Chrome/67.0.3396.99 Safari/537.36',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_HOST' => 'localhost:8080',
        ];

        $serverRequest = (new ServerRequestBuilder($server, $this->factory))
            ->addHeaders()
            ->build();
        
            $this->assertSame('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/67.0.3396.99 Chrome/67.0.3396.99 Safari/537.36', $serverRequest->getHeaderLine('user-agent'));
            $this->assertSame('application/json', $serverRequest->getHeaderLine('accept'));
            $this->assertSame('keep-alive', $serverRequest->getHeaderLine('connection'));
            $this->assertSame('localhost:8080', $serverRequest->getHeaderLine('host'));
    }

    public function uriParamsProvider()
    {
        return [
            'when_http_host' => [['HTTP_HOST' => 'www.test.com'], 'http://www.test.com'],
            'when_http_host_with_port' => [['HTTP_HOST' => 'www.test.com:8080'], 'http://www.test.com:8080'],
            'when_http_host_with_port_and_scheme' => [
                ['HTTPS' => 'on', 'HTTP_HOST' => 'www.test.com:8080'], 'https://www.test.com:8080'
            ],
            'when_http_host_with_port_and_request_scheme' => [
                ['REQUEST_SCHEME' => 'https', 'HTTP_HOST' => 'www.test.com:8080'], 'https://www.test.com:8080'
            ],
            'when_server_name' => [['SERVER_NAME' => 'test.com', 'SERVER_PORT' => 8080], 'http://test.com:8080'],
            'when_'

        ];
    } */

    /**
     * @dataProvider uriParamsProvider
     * 
     * @param array $server
     * @param string $expected
     */
    /* public function testCanAddUri(array $server, string $expected)
    {
        $serverRequest = (new ServerRequestBuilder($server, $this->factory))
            ->addUri()
            ->build();
        
        $this->assertSame($expected, (string) $serverRequest->getUri());
    } */

    /* public function testCanAddUri()
    {

    }

    public function testCanAddProtocolVersion()
    {

    }

    public function testCreateServerRequestFromGlobals()
    {
        $server = [
            'HTTPS' => 'on',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'SERVER_ADDR' => '172.22.0.4',
            'REMOTE_PORT' => '36852',
            'REMOTE_ADDR' => '172.22.0.1',
            'SERVER_SOFTWARE' => 'nginx/1.11.8',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'DOCUMENT_ROOT' => '/var/www/public',
            'DOCUMENT_URI' => '/index.php',
            'REQUEST_URI' => '/api/messagebox-schema',
            'PATH_TRANSLATED' => '/var/www/public',
            'PATH_INFO' => '',
            'SCRIPT_NAME' => '/index.php',
            'CONTENT_LENGTH' => '',
            'CONTENT_TYPE' => '',
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING' => '',
            'SCRIPT_FILENAME' => '/var/www/public/index.php',
            'FCGI_ROLE' => 'RESPONDER',
            'PHP_SELF' => '/index.php',
            'HTTP_COOKIE' => 'Set-Cookie: foo=bar; domain=test.com; path=/; expires=Wed, 30 Aug 2019 00:00:00 GMT',
            'HTTP_ACCEPT_LANGUAGE' => 'de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
            'HTTP_REFERER' => 'http://localhost:8080/index.html',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/67.0.3396.99 Chrome/67.0.3396.99 Safari/537.36',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_HOST' => 'localhost:8080',
        ];

        $parsedBody = [];
        $cookie = [];
        $files = [
            'logo' => [
                'tmp_name' => self::ASSETS_DIR . 'logo.png',
                'name' => 'bitframe-logo.png',
                'size' => 8316,
                'type' => 'image/png',
                'error' => 0,
            ],
        ];
        $body = '{"interest":[193775921.84923935,574124142.9716592,[false,"sharp",false,"fuel",false,12549561],true,965046052.5776172,true],"cage":1092844929.3166804,"press":"silk","waste":"blow","round":686075916.9727597,"our":1354781713}';

        $request = ServerRequestBuilder::fromSapi(
            $server,
            $this->factory,
            $parsedBody,
            $cookie,
            $files,
            $body
        );

        $uploadedFiles = $request->getUploadedFiles();

        $parsedCookie = [
            'foo' => 'bar',
        ];

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFiles['logo']);
        $this->assertEquals('bitframe-logo.png', $uploadedFiles['logo']->getClientFilename());

        $this->assertSame((new JsonMediaParser())->parse($body), $request->getParsedBody());

        $this->assertSame('https://localhost:8080', (string) $request->getUri());

        $this->assertSame('1.1', $request->getProtocolVersion());

        $this->assertSame($parsedCookie, $request->getCookieParams());
    } */
}