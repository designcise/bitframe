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

use \BitFrame\Delegate\CallableMiddlewareTrait;

class Controller implements MiddlewareInterface
{
	use CallableMiddlewareTrait;
	
	/**
     * {@inheritdoc}
     */
    public function process(
		ServerRequestInterface $request, 
		RequestHandlerInterface $handler
	): ResponseInterface
    {
		$response = $handler->handle($request);
		return $response->withHeader('process', 'true');
    }
	
    /**
     * {@inheritdoc}
     */
    public function action(
		ServerRequestInterface $request, 
		RequestHandlerInterface $handler
	): ResponseInterface
    {
		$response = $handler->handle($request);
		return $response->withHeader('action', 'true');
    }
}