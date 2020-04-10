<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Integration;

use BitFrame\Factory\HttpFactory;
use BitFrame\Factory\HttpFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use BitFrame\Emitter\SapiStreamEmitter;
use BitFrame\Http\Message\TextResponse;

use function get_class;

/**
 * @covers \BitFrame\Emitter\SapiStreamEmitter
 */
class SapiStreamEmitterTest extends AbstractSapiEmitterTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->emitter = new SapiStreamEmitter();
    }

    public function testEmitTextResponse()
    {
        $contents = 'Hello world';
        $response = (new TextResponse($contents))
            ->withStatus(200);
        
        ob_start();
        $this->emitter->emit($response);
        $this->assertSame('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertSame($contents, ob_get_clean());
    }

    public function testReturnsBodyWhenNotReadableButIsSeekable(): void
    {
        $body = $this->getMockBuilder(StreamInterface::class)
            ->onlyMethods(['isSeekable', 'isReadable', '__toString'])
            ->getMockForAbstractClass();

        $body->method('isSeekable')->willReturn(true);
        $body->method('isReadable')->willReturn(false);
        $body->method('__toString')->willReturn('hello');

        $response = HttpFactory::createResponse()
            ->withBody($body);

        $this->assertSame('hello', (string) $response->getBody());
    }
}
