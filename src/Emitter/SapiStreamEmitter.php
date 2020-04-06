<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 * @license   https://github.com/designcise/bitframe-zend-emitter/blob/master/LICENSE.md MIT License
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
    /** @var int */
    private int $maxBufferLength;
    
    /**
     * @param int $maxBufferLength
     */
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

    /**
     * @param StreamInterface $body
     */
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
