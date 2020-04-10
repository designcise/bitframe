<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Asset;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Named function.
 *
 * @param ServerRequestInterface $request
 * @param ResponseInterface $handler
 * 
 * @return ResponseInterface
 */
function helloWorldCallable(
    ServerRequestInterface $request, 
    ResponseInterface $handler
): ResponseInterface {
    $response = $handler->handle($request);
    $response->getBody()->write('Hello World!');

    return $response;
}