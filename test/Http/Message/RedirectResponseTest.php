<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Http\Message;

use PHPUnit\Framework\TestCase;
use BitFrame\Factory\HttpFactory;
use BitFrame\Http\Message\RedirectResponse;
use TypeError;

/**
 * @covers \BitFrame\Http\Message\RedirectResponse
 */
class RedirectResponseTest extends TestCase
{
    public function testConstructorAcceptsStringUriAndProduces302ResponseWithLocationHeader(): void
    {
        $response = new RedirectResponse('/foo/bar');
        self::assertSame(302, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertSame('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAcceptsUriInstanceAndProduces302ResponseWithLocationHeader(): void
    {
        $uri = HttpFactory::createUri('https://example.com:10082/foo/bar');
        $response = new RedirectResponse($uri);
        self::assertSame(302, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertSame((string) $uri, $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingAlternateStatusCode(): void
    {
        $response = new RedirectResponse('/foo/bar', 301);
        self::assertSame(301, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertSame('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testCanAddStatusAndHeaders(): void
    {
        $response = (new RedirectResponse('/foo/bar', 302))
            ->withHeader('X-Foo', ['Bar']);
        
        self::assertSame(302, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertSame('/foo/bar', $response->getHeaderLine('Location'));
        self::assertTrue($response->hasHeader('X-Foo'));
        self::assertSame('Bar', $response->getHeaderLine('X-Foo'));
    }

    public function testStaticCreate(): void
    {
        $response = RedirectResponse::create('/foo/bar', 307)
            ->withHeader('X-Foo', ['Bar']);
        
        self::assertSame(307, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertSame('/foo/bar', $response->getHeaderLine('Location'));
        self::assertTrue($response->hasHeader('X-Foo'));
        self::assertSame('Bar', $response->getHeaderLine('X-Foo'));
    }

    public function testWithStatusOverwritesOnePassedInThroughConstructor(): void
    {
        $response = (new RedirectResponse('/foo/bar', 302))
            ->withStatus(307);
        
        self::assertSame(307, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertSame('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function invalidUriProvider(): array
    {
        return [
            'null' => [null],
            'false' => [false],
            'true' => [true],
            'zero' => [0],
            'int' => [1],
            'zero-float' => [0.0],
            'float' => [1.1],
            'array' => [['/foo/bar']],
            'object' => [(object) ['/foo/bar']],
        ];
    }

    /**
     * @dataProvider invalidUriProvider
     */
    public function testConstructorRaisesExceptionOnInvalidUri($uri): void
    {
        $this->expectException(TypeError::class);
        new RedirectResponse($uri);
    }
}
