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

use BitFrame\Emitter\SapiEmitter;

/**
 * @covers \BitFrame\Message\SapiEmitter
 */
class SapiEmitterTest extends AbstractSapiEmitterTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->emitter = new SapiEmitter();
    }
}