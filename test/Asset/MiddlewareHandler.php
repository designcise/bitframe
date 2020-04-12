<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Asset;

use Psr\Http\Server\MiddlewareInterface;
use BitFrame\Factory\HttpFactory;
use BitFrame\Http\MiddlewareDecoratorTrait;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

class MiddlewareHandler implements RequestHandlerInterface
{
    use MiddlewareDecoratorTrait;

    private $middlewares;

    private ResponseInterface $response;

    public function __construct($middleware)
    {
        $this->middlewares = [...$this->getUnpackedMiddleware($middleware)];
        $this->response = HttpFactory::createResponse();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($middleware = array_shift($this->middlewares)) {
            $this->response = $middleware->process($request, $this);
        }

        return $this->response;
    }
}
