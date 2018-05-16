<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Delegate;

use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

use BitFrame\Delegate\CallableDelegate;

/**
 * Decorate PSR-7 style callable delegates as PSR-15 
 * compatible, processable http requests.
 */
trait CallableMiddlewareTrait
{
    /**
     * PSR-7 style callable.
     *
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $next callable
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface 
    {
        return $this->process($request, new CallableDelegate($next, $response));
    }
    
    /**
     * PSR-15 based middleware implementation.
     *
     * @param $request ServerRequestInterface
     * @param $handler RequestHandlerInterface
     *
     * @return ResponseInterface
     */
    abstract public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}