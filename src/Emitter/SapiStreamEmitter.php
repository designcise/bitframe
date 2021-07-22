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

use Psr\Http\Message\{ResponseInterface, StreamInterface};

use function headers_sent;

/**
 * Emits response in chunks based on a fixed buffer length.
 */
class SapiStreamEmitter extends AbstractSapiEmitter
{
    private int $maxBufferLength;

    public function __construct(int $maxBufferLength = 8192)
    {
        $this->maxBufferLength = $maxBufferLength;
    }

    /**
     * {@inheritdoc}
     */
    public function emit(ResponseInterface $response): void
    {
        if (! headers_sent()) {
            $this->emitHeaders($response);
        }

        $this->emitBody($response->getBody());
    }

    private function emitBody(StreamInterface $body): void
    {
        if ($body->isSeekable()) {
            $body->rewind();
        }

        if (! $body->isReadable()) {
            echo $body;
            return;
        }

        while (! $body->eof()) {
            echo $body->read($this->maxBufferLength);
        }
    }
}
