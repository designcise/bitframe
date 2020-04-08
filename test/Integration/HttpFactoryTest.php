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

/**
 * @covers \BitFrame\Factory\HttpFactory
 */
class HttpFactoryTest extends TestCase
{
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
        $this->assertSame('/', (string)$request->getUri());
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
        $this->assertSame((string)$uri, (string)$request->getUri());
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
        $request = HttpFactory::createServerRequestFromGlobals(
            $serverParams
        );

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(
            'scheme://host:81/path?query#fragment',
            (string)$request->getUri()
        );
        $this->assertSame($serverParams, $request->getServerParams());
    }
}
