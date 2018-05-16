<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Test\Asset;

use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use \Psr\Http\Server\{RequestHandlerInterface, MiddlewareInterface};

class InteropMiddleware implements MiddlewareInterface
{
    public function run(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        echo 'via run';
        return $this->process($request, $handler);
    }
    
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        return $handler->handle($request);
    }
}