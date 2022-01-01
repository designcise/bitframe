<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Emitter;

use BitFrame\Emitter\SapiEmitter;

/**
 * @covers \BitFrame\Emitter\SapiEmitter
 */
class SapiEmitterTest extends AbstractSapiEmitterTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->emitter = new SapiEmitter();
    }
}
