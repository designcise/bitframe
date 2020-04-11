<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Asset;

use Closure;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{RequestHandlerInterface, MiddlewareInterface};

trait HelloWorldMiddlewareTrait
{
    private function getHelloWorldMiddlewareAsPsr15(): MiddlewareInterface
    {
        return new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $response = $handler->handle($request);
                $response->getBody()->write('Hello World!');

                return $response;
            }
        };
    }

    private function getHelloWorldMiddlewareAsClosure(): Closure
    {
        return static function (
            ServerRequestInterface $request,
            RequestHandlerInterface $handler
        ): ResponseInterface {
            $response = $handler->handle($request);
            $response->getBody()->write('Hello World!');

            return $response;
        };
    }
}
