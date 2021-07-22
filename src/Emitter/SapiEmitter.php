<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Emitter;

use Psr\Http\Message\ResponseInterface;

use function headers_sent;

/**
 * Simple sapi emitter.
 */
class SapiEmitter extends AbstractSapiEmitter
{
    /**
     * {@inheritdoc}
     */
    public function emit(ResponseInterface $response): void
    {
        if (! headers_sent()) {
            $this->emitHeaders($response);
        }

        echo $response->getBody();
    }
}
