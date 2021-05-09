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
use BitFrame\Http\Message\JsonpResponse;
use JsonException;
use InvalidArgumentException;

use function json_encode;
use function fopen;

/**
 * @covers \BitFrame\Http\Message\JsonpResponse
 */
class JsonpResponseTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testConstructorAcceptsDataAndCreatesJsonEncodedMessageBody(): void
    {
        $data = ['nested' => ['json' => ['tree']]];
        $json = 'test({"nested":{"json":["tree"]}})';

        $response = new JsonpResponse($data, 'test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            'application/javascript; charset=utf-8',
            $response->getHeaderLine('content-type')
        );
        $this->assertSame($json, (string) $response->getBody());
    }

    public function scalarValuesForJsonProvider(): array
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
     *
     * @param mixed $value
     *
     * @throws JsonException
     */
    public function testScalarValuePassedToConstructorJsonEncodesDirectly($value): void
    {
        $response = new JsonpResponse($value, 'test');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            'application/javascript; charset=utf-8',
            $response->getHeaderLine('content-type')
        );
        // 15 is the default mask used by JsonpResponse
        $this->assertSame(
            'test(' . json_encode($value, 15) . ')',
            (string) $response->getBody()
        );
    }

    /**
     * @throws JsonException
     */
    public function testCanAddStatusAndHeader(): void
    {
        $response = (new JsonpResponse([], 'test'))
            ->withStatus(404)
            ->withHeader('content-type', 'foo/javascript');
        
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('foo/javascript', $response->getHeaderLine('content-type'));
    }

    /**
     * @throws JsonException
     */
    public function testStaticCreateWithCustomContentType(): void
    {
        $response = JsonpResponse::create([], 'test')
            ->withHeader('content-type', 'text/javascript');
        
        $this->assertSame('text/javascript', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @throws JsonException
     */
    public function testThrowsJsonExceptionForResources(): void
    {
        $resource = fopen('php://memory', 'r');
        $this->expectException(JsonException::class);
        new JsonpResponse($resource, 'test');
    }

    public function testThrowsExceptionForNonSerializableData(): void
    {
        $data = [
            'stream' => fopen('php://memory', 'r'),
        ];
        $this->expectException(JsonException::class);
        new JsonpResponse($data, 'test');
    }

    /**
     * @throws JsonException
     */
    public function testThrowsExceptionWhenCallbackIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JsonpResponse([], '');
    }

    public function invalidCallbackProvider(): array
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
     *
     * @param mixed $callbackName
     *
     * @throws JsonException
     */
    public function testThrowsExceptionWhenCallbackIsInvalid($callbackName): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JsonpResponse([], $callbackName);
    }

    public function valuesToJsonEncodeProvider(): array
    {
        return [
            'uri' => ['https://example.com/foo?bar=baz&baz=bat', 'uri'],
            'html' => ['<p class="test">content</p>', 'html'],
            'string' => ["Don't quote!", 'string'],
        ];
    }

    /**
     * @dataProvider valuesToJsonEncodeProvider
     *
     * @param string $value
     * @param string $key
     *
     * @throws JsonException
     */
    public function testUsesSaneDefaultJsonEncodingFlags($value, $key): void
    {
        $defaultFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES;
        $response = new JsonpResponse([$key => $value], 'test');
        $stream = $response->getBody();
        $contents = (string) $stream;
        $expected = json_encode($value, $defaultFlags);
        $this->assertStringContainsString($expected, $contents);
    }

    public function testJsonEncodeFlags(): void
    {
        $response = new JsonpResponse('<>\'&"', 'test');
        $this->assertEquals(
            'test("\u003C\u003E\u0027\u0026\u0022")',
            (string) $response->getBody()
        );
    }
}
