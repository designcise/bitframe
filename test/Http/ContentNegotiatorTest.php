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
use BitFrame\Http\ContentNegotiator;
use BitFrame\Factory\HttpFactory;
use BitFrame\Parser\MediaParserInterface;
use BitFrame\Parser\{DefaultMediaParser, JsonMediaParser, XmlMediaParser};

/**
 * @covers \BitFrame\Http\ContentNegotiator
 */
class ContentNegotiatorTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testAddMediaParser(): void
    {
        $parser = $this->getMockBuilder(MediaParserInterface::class)
            ->getMock();

        $parser
            ->method('parse')
            ->willReturn(['foo' => 'bar']);

        $contentType = ContentNegotiator::CONTENT_TYPE_HTML;

        ContentNegotiator::addMediaParser($contentType, $parser);

        $this->assertInstanceOf(
            get_class($parser),
            ContentNegotiator::getMediaParserForContentType($contentType)
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddMediaParserByClassName(): void
    {
        $parser = $this->getMockBuilder(MediaParserInterface::class)
            ->getMock();

        $parser
            ->method('parse')
            ->willReturn(['foo' => 'bar']);

        $contentType = ContentNegotiator::CONTENT_TYPE_HTML;
        $parserClass = get_class($parser);

        ContentNegotiator::addMediaParser($contentType, $parserClass);

        $this->assertInstanceOf(
            $parserClass,
            ContentNegotiator::getMediaParserForContentType($contentType)
        );
    }

    public function mediaParserForContentTypeProvider(): array
    {
        return [
            'html' => [ContentNegotiator::CONTENT_TYPE_HTML, DefaultMediaParser::class],
            'json' => [ContentNegotiator::CONTENT_TYPE_JSON, JsonMediaParser::class],
            'xml' => [ContentNegotiator::CONTENT_TYPE_XML, XmlMediaParser::class],
            'text' => [ContentNegotiator::CONTENT_TYPE_TEXT, DefaultMediaParser::class],
        ];
    }

    /**
     * @dataProvider mediaParserForContentTypeProvider
     *
     * @param string $contentType
     * @param string $parserClassName
     */
    public function testGetMediaParserForContentType(
        string $contentType,
        string $parserClassName
    ): void {
        $this->assertInstanceOf(
            $parserClassName,
            ContentNegotiator::getMediaParserForContentType($contentType)
        );
    }

    public function preferredMediaParserProvider(): array
    {
        return [
            'text_html' => ['text/html', DefaultMediaParser::class],
            'app_xhtml_xml' => ['application/xhtml+xml', DefaultMediaParser::class],

            'app_json' => ['application/json', JsonMediaParser::class],
            'text_json' => ['text/json', JsonMediaParser::class],
            'app_x_json' => ['application/x-json', JsonMediaParser::class],

            'text_xml' => ['text/xml', XmlMediaParser::class],
            'app_xml' => ['application/xml', XmlMediaParser::class],
            'app_x_xml' => ['application/x-xml', XmlMediaParser::class],

            'text_plain' => ['text/plain', DefaultMediaParser::class],
            'form_urlencoded' => ['application/x-www-form-urlencoded', DefaultMediaParser::class],
            'form_data' => ['multipart/form-data', DefaultMediaParser::class],
        ];
    }

    /**
     * @dataProvider preferredMediaParserProvider
     *
     * @param string $mimeType
     * @param string $parserClassName
     */
    public function testGetPreferredMediaParserFromRequest(
        string $mimeType, 
        string $parserClassName
    ): void {
        $request = HttpFactory::createServerRequest('GET', '/')
            ->withHeader('accept', $mimeType);
        
        $this->assertInstanceOf(
            $parserClassName, 
            ContentNegotiator::getPreferredMediaParserFromRequest($request)
        );
    }

    public function preferredContentTypeProvider(): array
    {
        $html = ContentNegotiator::CONTENT_TYPE_HTML;
        $json = ContentNegotiator::CONTENT_TYPE_JSON;
        $xml = ContentNegotiator::CONTENT_TYPE_XML;
        $text = ContentNegotiator::CONTENT_TYPE_TEXT;

        return [
            'text_html' => ['text/html', $html],
            'app_xhtml_xml' => ['application/xhtml+xml', $html],

            'app_json' => ['application/json', $json],
            'text_json' => ['text/json', $json],
            'app_x_json' => ['application/x-json', $json],

            'text_xml' => ['text/xml', $xml],
            'app_xml' => ['application/xml', $xml],
            'app_x_xml' => ['application/x-xml', $xml],

            'text_plain' => ['text/plain', $text],
        ];
    }

    /**
     * @dataProvider preferredContentTypeProvider
     *
     * @param string $mimeType
     * @param string $contentType
     */
    public function testGetPreferredContentTypeFromRequest(
        string $mimeType, 
        string $contentType
    ): void {
        $request = HttpFactory::createServerRequest('GET', '/')
            ->withHeader('accept', $mimeType);
        
        $this->assertSame(
            $contentType, 
            ContentNegotiator::getPreferredContentTypeFromRequest($request)
        );
    }

    /**
     * @param string $mimeType
     * @param string $contentType
     */
    public function testGetPreferredContentTypeFromRequestWhenNoAcceptHeaderExists(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/');

        $this->assertSame(
            ContentNegotiator::CONTENT_TYPE_HTML,
            ContentNegotiator::getPreferredContentTypeFromRequest($request)
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddContentType(): void
    {
        $mime = 'foo/bar';

        $request = HttpFactory::createServerRequest('GET', '/')
            ->withHeader('accept', $mime);
        
        ContentNegotiator::addContentType('json', $mime);

        $this->assertInstanceOf(
            JsonMediaParser::class, 
            ContentNegotiator::getPreferredMediaParserFromRequest($request)
        );
    }
}
