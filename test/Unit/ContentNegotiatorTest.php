<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Unit;

use PHPUnit\Framework\TestCase;
use BitFrame\Http\ContentNegotiator;
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
    public function testAddMediaParser()
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
     * @dataProvider mediaParserForContentTypeProvider
     */
    public function testGetMediaParserForContentType(
        string $contentType, 
        string $parserClassName
    ) {
        $this->assertInstanceOf(
            $parserClassName, 
            ContentNegotiator::getMediaParserForContentType($contentType)
        );
    }

    public function mediaParserForContentTypeProvider()
    {
        return [
            'html' => [ContentNegotiator::CONTENT_TYPE_HTML, DefaultMediaParser::class],
            'json' => [ContentNegotiator::CONTENT_TYPE_JSON, JsonMediaParser::class],
            'xml' => [ContentNegotiator::CONTENT_TYPE_XML, XmlMediaParser::class],
            'text' => [ContentNegotiator::CONTENT_TYPE_TEXT, DefaultMediaParser::class],
        ];
    }
}
