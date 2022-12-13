<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Emitter;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{RequestHandlerInterface, MiddlewareInterface};

use function header;

/**
 * Emit http response.
 *
 * Use as middleware or standalone.
 */
abstract class AbstractSapiEmitter implements MiddlewareInterface
{
    abstract public function emit(ResponseInterface $response): void;

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $response = $handler->handle($request);
        $this->emit($response);
        return $response;
    }

    /**
     * Emit response headers, including the status line header.
     *
     * If the header value is an array with multiple values, each is sent
     * in such a way as to create aggregate headers (instead of overwriting
     * the previous).
     *
     * @param ResponseInterface $response
     */
    protected function emitHeaders(ResponseInterface $response): void
    {
        $headers = $response->getHeaders();
        $statusCode = $response->getStatusCode();

        foreach ($headers as $name => $values) {
            $first = ($name !== 'Set-Cookie');
            foreach ($values as $value) {
                header("$name: $value", $first, $statusCode);
                $first = false;
            }
        }

        $protocol = $response->getProtocolVersion();
        $reason = $response->getReasonPhrase();

        // status line should be emitted at the end in order to prevent PHP from
        // changing the status code of the emitted response
        header("HTTP/$protocol $statusCode $reason", true, $statusCode);
    }
}
