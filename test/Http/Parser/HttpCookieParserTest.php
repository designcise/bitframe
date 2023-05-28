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

use BitFrame\Http\Parser\HttpCookieParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BitFrame\Http\Parser\HttpCookieParser
 */
class HttpCookieParserTest extends TestCase
{
    public function cookiesProvider(): array
    {
        return [
            'no cookies' => ['', []],
            'HTTP_COOKIE array' => ['foo_bar=baz', ['foo_bar' => 'baz']],
            'url encoded HTTP_COOKIE string' => ['Set-Cookie%3A%20test%3D1234;', []],
            'HTTP_COOKIE string' => [
                'Set-Cookie: foo=bar; domain=test.com; path=/; expires=Wed, 30 Aug 2019 00:00:00 GMT',
                ['foo' => 'bar', 'domain' => 'test.com', 'path' => '/']
            ],
            'ows without fold' => [
                "\tfoo=bar ",
                ['foo' => 'bar'],
            ],
            'url encoded value' => [
                'foo=bar%3B+',
                ['foo' => 'bar; '],
            ],
            'double quoted value' => [
                'foo="bar"',
                ['foo' => 'bar'],
            ],
            'multiple pairs' => [
                'foo=bar; baz="bat"; bau=bai',
                ['foo' => 'bar', 'baz' => 'bat', 'bau' => 'bai'],
            ],
            'same-name pairs' => [
                'foo=bar; foo="bat"',
                ['foo' => 'bat'],
            ],
            'period in name' => [
                'foo.bar=baz',
                ['foo.bar' => 'baz'],
            ],
        ];
    }

    /**
     * @dataProvider cookiesProvider
     *
     * @param string $cookieParams
     * @param array $expected
     */
    public function testParseHttpCookie(string $cookieParams, array $expected): void
    {
        $parsed = HttpCookieParser::parse($cookieParams);

        $this->assertSame($expected, $parsed);
    }
}
