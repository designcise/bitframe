<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Unit;

use PHPUnit\Framework\TestCase;
use BitFrame\Http\Message\JsonpResponse;
use JsonException;
use InvalidArgumentException;

/**
 * @covers \BitFrame\Http\Message\JsonpResponse
 */
class JsonpResponseTest extends TestCase
{
    public function testConstructorAcceptsDataAndCreatesJsonEncodedMessageBody()
    {
        $data = ['nested' => ['json' => ['tree']]];
        $json = 'test({"nested":{"json":["tree"]}})';

        $response = new JsonpResponse($data, 'test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/javascript; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertSame($json, (string) $response->getBody());
    }

    public function scalarValuesForJsonProvider()
    {
        return [
            'null' => [null],
            'false' => [false],
            'true' => [true],
            'zero' => [0],
            'int' => [1],
            'zero-float' => [0.0],
            'float' => [1.1],
            'empty-string' => [''],
            'string' => ['string'],
        ];
    }
    
    /**
     * @dataProvider scalarValuesForJsonProvider
     */
    public function testScalarValuePassedToConstructorJsonEncodesDirectly($value)
    {
        $response = new JsonpResponse($value, 'test');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/javascript; charset=utf-8', $response->getHeaderLine('content-type'));
        // 15 is the default mask used by JsonpResponse
        $this->assertSame('test(' . json_encode($value, 15) . ')', (string) $response->getBody());
    }

    public function testCanAddStatusAndHeader()
    {
        $response = (new JsonpResponse([], 'test'))
            ->withStatus(404)
            ->withHeader('content-type', 'foo/javascript');
        
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('foo/javascript', $response->getHeaderLine('content-type'));
    }

    public function testStaticCreateWithCustomContentType()
    {
        $response = JsonpResponse::create([], 'test')
            ->withHeader('content-type', 'text/javascript');
        
        $this->assertSame('text/javascript', $response->getHeaderLine('Content-Type'));
    }

    public function testThrowsJsonExceptionForResources()
    {
        $resource = fopen('php://memory', 'r');
        $this->expectException(JsonException::class);
        new JsonpResponse($resource, 'test');
    }

    public function testThrowsExceptionForNonSerializableData()
    {
        $data = [
            'stream' => fopen('php://memory', 'r'),
        ];
        $this->expectException(JsonException::class);
        new JsonpResponse($data, 'test');
    }

    public function testThrowsExceptionWhenCallbackIsEmpty()
    {
        $this->expectException(InvalidArgumentException::class);
        new JsonpResponse([], '');
    }

    public function invalidCallbackProvider()
    {
        return [
            ['\u200C\u200D'], ['+invalid'], ['true'], ['false'],
            ['instanceof'], ['break'], ['do'], ['instanceof'], 
            ['typeof'], ['case'], ['else'], ['new'], 
            ['var'], ['catch'], ['finally'], ['return'], 
            ['void'], ['continue'], ['for'], ['switch'], 
            ['while'], ['debugger'], ['function'], ['this'], 
            ['with'], ['default'], ['if'], ['throw'], 
            ['delete'], ['in'], ['try'], ['class'], 
            ['enum'], ['extends'], ['super'], ['const'], 
            ['export'], ['import'], ['implements'], ['let'], 
            ['private'], ['public'], ['yield'], ['interface'], 
            ['package'], ['protected'], ['static'], ['null'], 
        ];
    }

    /**
     * @dataProvider invalidCallbackProvider
     */
    public function testThrowsExceptionWhenCallbackIsInvalid($callbackName)
    {
        $this->expectException(InvalidArgumentException::class);
        new JsonpResponse([], $callbackName);
    }

    public function valuesToJsonEncodeProvider()
    {
        return [
            'uri' => ['https://example.com/foo?bar=baz&baz=bat', 'uri'],
            'html' => ['<p class="test">content</p>', 'html'],
            'string' => ["Don't quote!", 'string'],
        ];
    }

    /**
     * @dataProvider valuesToJsonEncodeProvider
     */
    public function testUsesSaneDefaultJsonEncodingFlags($value, $key)
    {
        $defaultFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES;
        $response = new JsonpResponse([$key => $value], 'test');
        $stream = $response->getBody();
        $contents = (string) $stream;
        $expected = json_encode($value, $defaultFlags);
        $this->assertStringContainsString($expected, $contents);
    }

    public function testJsonEncodeFlags()
    {
        $response = new JsonpResponse('<>\'&"', 'test');
        $this->assertEquals('test("\u003C\u003E\u0027\u0026\u0022")', (string) $response->getBody());
    }
}