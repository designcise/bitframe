<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Integration;

use BitFrame\Emitter\SapiStreamEmitter;
use BitFrame\Http\Message\TextResponse;

/**
 * @covers \BitFrame\Message\SapiStreamEmitter
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
}