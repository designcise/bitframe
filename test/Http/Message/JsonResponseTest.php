<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Http\Message;

use PHPUnit\Framework\TestCase;
use BitFrame\Http\Message\JsonResponse;
use JsonException;

use function json_encode;

use const JSON_HEX_TAG;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_AMP;
use const JSON_UNESCAPED_SLASHES;

/**
 * @covers \BitFrame\Http\Message\JsonResponse
 */
class JsonResponseTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testConstructorAcceptsDataAndCreatesJsonEncodedMessageBody(): void
    {
        $data = ['nested' => ['json' => ['tree']]];
        $json = '{"nested":{"json":["tree"]}}';

        $response = new JsonResponse($data);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            'application/json; charset=utf-8',
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
        $response = new JsonResponse($value);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('content-type'));
        // 15 is the default mask used by JsonResponse
        $this->assertSame(json_encode($value, 15), (string) $response->getBody());
    }

    public function testCanAddStatusAndHeader(): void
    {
        $response = (new JsonResponse())
            ->withStatus(404)
            ->withHeader('content-type', 'foo/json');
        
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('foo/json', $response->getHeaderLine('content-type'));
    }

    /**
     * @throws JsonException
     */
    public function testStaticCreateWithCustomContentType(): void
    {
        $response = JsonResponse::create()
            ->withHeader('content-type', 'application/vnd.acme.blog-v1+json');
        
        $this->assertSame(
            'application/vnd.acme.blog-v1+json',
            $response->getHeaderLine('Content-Type')
        );
    }

    public function testThrowsJsonExceptionForResources(): void
    {
        $resource = fopen('php://memory', 'r');
        $this->expectException(JsonException::class);
        new JsonResponse($resource);
    }

    public function testThrowsExceptionForNonSerializableData(): void
    {
        $data = [
            'stream' => fopen('php://memory', 'r'),
        ];
        $this->expectException(JsonException::class);
        new JsonResponse($data);
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
        $response = new JsonResponse([$key => $value]);
        $stream = $response->getBody();
        $contents = (string) $stream;
        $expected = json_encode($value, $defaultFlags);
        $this->assertStringContainsString($expected, $contents);
    }

    public function testJsonEncodeFlags(): void
    {
        $response = new JsonResponse('<>\'&"');
        $this->assertEquals(
            '"\u003C\u003E\u0027\u0026\u0022"',
            (string) $response->getBody()
        );
    }
}
