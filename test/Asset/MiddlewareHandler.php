<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Asset;

use BitFrame\Http\MiddlewareDecoratorTrait;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{ResponseFactoryInterface, ServerRequestInterface, ResponseInterface};

use function array_shift;

class MiddlewareHandler implements RequestHandlerInterface
{
    use MiddlewareDecoratorTrait;

    private array $middlewares;

    private ResponseInterface $response;

    public function __construct($middleware, ResponseFactoryInterface $factory)
    {
        $this->middlewares = [...$this->getUnpackedMiddleware($middleware)];
        $this->response = $factory->createResponse();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($middleware = array_shift($this->middlewares)) {
            $this->response = $middleware->process($request, $this);
        }

        return $this->response;
    }
}
