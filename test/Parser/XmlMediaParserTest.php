<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Parser;

use SimpleXMLElement;
use PHPUnit\Framework\TestCase;
use BitFrame\Parser\XmlMediaParser;

/**
 * @covers \BitFrame\Parser\XmlMediaParser
 */
class XmlMediaParserTest extends TestCase
{
    public function testCanParseString(): void
    {
        $input = <<<XML
<?xml version='1.0'?> 
<foo>
    <bar>Hello World!</bar>
    <baz>
        <qux>Test</qux>
    </baz>
</foo>
XML;

        $parser = new XmlMediaParser();
        $xml = $parser->parse($input);

        $this->assertInstanceOf(SimpleXMLElement::class, $xml);
        $this->assertSame('foo', $xml->getName());
        $this->assertSame('Hello World!', (string) $xml->bar[0]);
        $this->assertSame('Test', (string) $xml->baz[0]->qux);
    }

    public function testIgnoresCDATA(): void
    {
        $input = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar><![CDATA[Hello World!]]></bar>
</foo>
XML;

        $parser = new XmlMediaParser();
        $xml = $parser->parse($input);

        $this->assertInstanceOf(SimpleXMLElement::class, $xml);
        $this->assertSame('foo', $xml->getName());
        $this->assertSame('Hello World!', (string) $xml->bar[0]);
    }
}
