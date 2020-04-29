<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use BitFrame\Http\MediaParserNegotiator;
use BitFrame\Parser\MediaParserInterface;
use BitFrame\Parser\{DefaultMediaParser, JsonMediaParser, XmlMediaParser};

use function get_class;

/**
 * @covers \BitFrame\Http\MediaParserNegotiator
 */
class MediaParserNegotiatorTest extends TestCase
{
    public function parserNameProvider(): array
    {
        return [
            'add new parser' => ['newMediaParser'],
            'replace DefaultMediaParser' => [MediaParserNegotiator::CONTENT_TYPE_DEFAULT],
            'replace JsonMediaParser' => [MediaParserNegotiator::CONTENT_TYPE_JSON],
            'replace XmlMediaParser' => [MediaParserNegotiator::CONTENT_TYPE_XML],
        ];
    }

    /**
     * @runInSeparateProcess
     * @dataProvider parserNameProvider
     *
     * @param string $parserName
     */
    public function testAddNewOrUpdateExistingParser(string $parserName): void
    {
        $parser = new class implements MediaParserInterface {
            public const MIMES = ['text/made-up'];
            public function parse(string $input)
            {
                return "foo({$input})";
            }
        };

        MediaParserNegotiator::add($parserName, get_class($parser));

        /** @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getHeader'])
            ->getMockForAbstractClass();

        $request
            ->method('getHeader')
            ->with('accept')
            ->willReturn(['text/made-up']);

        $parser = MediaParserNegotiator::fromRequest($request);

        $this->assertSame('foo(bar)', $parser->parse('bar'));
    }

    public function preferredMediaParserProvider(): array
    {
        return [
            'text/html' => ['text/html', DefaultMediaParser::class],
            'app/xhtml+xml' => ['application/xhtml+xml', DefaultMediaParser::class],

            'app/json' => ['application/json', JsonMediaParser::class],
            'text/json' => ['text/json', JsonMediaParser::class],
            'app/x-json' => ['application/x-json', JsonMediaParser::class],

            'text/xml' => ['text/xml', XmlMediaParser::class],
            'app/xml' => ['application/xml', XmlMediaParser::class],
            'app/x-xml' => ['application/x-xml', XmlMediaParser::class],

            'text/plain' => ['text/plain', DefaultMediaParser::class],
            'app/form-urlencoded' => ['application/x-www-form-urlencoded', DefaultMediaParser::class],
            'app/form-data' => ['multipart/form-data', DefaultMediaParser::class],
        ];
    }

    /**
     * @dataProvider preferredMediaParserProvider
     */
    public function testFromRequest(string $mime, string $expectedParser): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getHeader'])
            ->getMockForAbstractClass();

        $request
            ->method('getHeader')
            ->with('accept')
            ->willReturn([$mime]);

        $this->assertInstanceOf($expectedParser, MediaParserNegotiator::fromRequest($request));
    }
}
