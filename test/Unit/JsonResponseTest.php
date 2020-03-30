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
use BitFrame\Http\Message\JsonResponse;
use JsonException;

/**
 * @covers \BitFrame\Http\Message\JsonResponse
 */
class JsonResponseTest extends TestCase
{
    public function testConstructorAcceptsDataAndCreatesJsonEncodedMessageBody()
    {
        $data = ['nested' => ['json' => ['tree']]];
        $json = '{"nested":{"json":["tree"]}}';

        $response = new JsonResponse($data);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('content-type'));
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
        $response = new JsonResponse($value);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('content-type'));
        // 15 is the default mask used by JsonResponse
        $this->assertSame(json_encode($value, 15), (string) $response->getBody());
    }

    public function testCanAddStatusAndHeader()
    {
        $response = (new JsonResponse())
            ->withStatus(404)
            ->withHeader('content-type', 'foo/json');
        
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('foo/json', $response->getHeaderLine('content-type'));
    }

    public function testStaticCreateWithCustomContentType()
    {
        $response = JsonResponse::create()
            ->withHeader('content-type', 'application/vnd.acme.blog-v1+json');
        
        $this->assertSame('application/vnd.acme.blog-v1+json', $response->getHeaderLine('Content-Type'));
    }

    public function testThrowsJsonExceptionForResources()
    {
        $resource = fopen('php://memory', 'r');
        $this->expectException(JsonException::class);
        new JsonResponse($resource);
    }

    public function testThrowsExceptionForNonSerializableData()
    {
        $data = [
            'stream' => fopen('php://memory', 'r'),
        ];
        $this->expectException(JsonException::class);
        new JsonResponse($data);
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
        $response = new JsonResponse([$key => $value]);
        $stream = $response->getBody();
        $contents = (string) $stream;
        $expected = json_encode($value, $defaultFlags);
        $this->assertStringContainsString($expected, $contents);
    }

    public function testJsonEncodeFlags()
    {
        $response = new JsonResponse('<>\'&"');
        $this->assertEquals('"\u003C\u003E\u0027\u0026\u0022"', (string) $response->getBody());
    }
}