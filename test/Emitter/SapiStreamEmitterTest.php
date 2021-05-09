<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Emitter;

use Mockery;
use Psr\Http\Message\StreamInterface;
use BitFrame\Factory\HttpFactory;
use BitFrame\Emitter\SapiStreamEmitter;
use BitFrame\Http\Message\TextResponse;

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

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testEmitTextResponse()
    {
        $contents = 'Hello world';
        $response = (new TextResponse($contents))
            ->withStatus(200);
        
        ob_start();
        $this->emitter->emit($response);
        self::assertSame('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertSame($contents, ob_get_clean());
    }

    /*public function testReturnsBodyWhenNotReadableButIsSeekable(): void
    {
        $stream = HttpFactory::createStream('Hello world!');
        $mockedStream = Mockery::mock($stream, StreamInterface::class)->makePartial();
        $mockedStream->shouldReceive('isSeekable')->andReturn(true);
        $mockedStream->shouldReceive('isReadable')->andReturn(false);

        $response = HttpFactory::createResponse()
            ->withBody($mockedStream);

        ob_start();
        $this->emitter->emit($response);
        self::assertSame('Hello world!', ob_get_clean());
    }*/
}
