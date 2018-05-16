<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 *
 * @author    Zend Framework
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-template/blob/master/LICENSE.md New BSD License
 */

namespace BitFrame\Delegate;

use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use \Psr\Http\Server\RequestHandlerInterface;

/**
 * Decorate callable delegates as http delegates in order to process
 * incoming requests.
 */
class CallableDelegate implements RequestHandlerInterface
{
    /** @var callable */
    private $delegate;

    /** @var ResponseInterface */
    private $response;

    /**
     * @param callable $delegate
     * @param ResponseInterface $response
     */
    public function __construct(callable $delegate, ResponseInterface $response)
    {
        $this->delegate = $delegate;
        $this->response = $response;
    }

    /**
     * Proxies to the underlying callable delegate to process a request.
     *
     * {@inheritDoc}
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $delegate = $this->delegate;
        return $delegate($request, $this->response);
    }
}
