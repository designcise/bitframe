<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Parser;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use BitFrame\Parser\{
    MediaParserInterface,
    MediaParserNegotiator,
    DefaultMediaParser,
    JsonMediaParser,
    XmlMediaParser
};
use InvalidArgumentException;

use function get_class;
use function json_decode;

/**
 * @covers \BitFrame\Parser\MediaParserNegotiator
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
     * @dataProvider parserNameProvider
     *
     * @param string $parserName
     */
    public function testAddNewOrUpdateExistingParser(string $parserName): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getHeader'])
            ->getMockForAbstractClass();

        $request
            ->method('getHeader')
            ->with('accept')
            ->willReturn(['text/made-up']);

        $parser = new class implements MediaParserInterface {
            public const MIMES = ['text/made-up'];
            public function parse(string $input)
            {
                return "foo({$input})";
            }
        };

        $negotiator = new MediaParserNegotiator($request);
        $negotiator->add($parserName, get_class($parser));

        $this->assertSame('foo(bar)', $negotiator->parse('bar'));
    }

    public function testParse(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getHeader'])
            ->getMockForAbstractClass();

        $request
            ->method('getHeader')
            ->with('accept')
            ->willReturn(['application/json']);

        $parser = new class implements MediaParserInterface {
            public const MIMES = ['application/json'];
            public function parse(string $input)
            {
                return json_decode('{"arg":"' . $input . '"}', true);
            }
        };

        $negotiator = new MediaParserNegotiator($request);
        $negotiator->add(MediaParserNegotiator::CONTENT_TYPE_JSON, get_class($parser));

        $this->assertSame(['arg' => 'bar'], $negotiator->parse('bar'));
    }

    public function testAddNewInvalidParserShouldThrowException(): void
    {
        $invalidParser = new class {};

        /** @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMockForAbstractClass();
        $negotiator = new MediaParserNegotiator($request);

        $this->expectException(InvalidArgumentException::class);

        $negotiator->add('whatever', get_class($invalidParser));
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
     *
     * @param string $mime
     * @param string $expectedParser
     */
    public function testGetPreferredMediaParser(string $mime, string $expectedParser): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getHeader'])
            ->getMockForAbstractClass();

        $request
            ->method('getHeader')
            ->with('accept')
            ->willReturn([$mime]);

        $negotiator = new MediaParserNegotiator($request);

        $this->assertInstanceOf($expectedParser, $negotiator->getPreferredMediaParser());
    }

    public function testGetsDefaultParserWhenAcceptHeaderNotPresent(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getHeader'])
            ->getMockForAbstractClass();

        $request
            ->method('getHeader')
            ->with('accept')
            ->willReturn([]);

        $negotiator = new MediaParserNegotiator($request);

        $this->assertInstanceOf(
            DefaultMediaParser::class,
            $negotiator->getPreferredMediaParser()
        );
    }

    public function testGetsCachedParserOnRepeatCalls(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getHeader'])
            ->getMockForAbstractClass();

        $request
            ->method('getHeader')
            ->with('accept')
            ->willReturn([]);

        $negotiator = new MediaParserNegotiator($request);

        $preferredProvider = $negotiator->getPreferredMediaParser();

        $this->assertSame($preferredProvider, $negotiator->getPreferredMediaParser());
    }
}
