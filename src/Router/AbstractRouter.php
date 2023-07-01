<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Router;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use BitFrame\Http\MiddlewareDecoratorTrait;

/**
 * Basic router implementation.
 */
abstract class AbstractRouter
{
    use MiddlewareDecoratorTrait;
    use RouterTrait;

    public function use(
        array|string $methods,
        array|string|callable|MiddlewareInterface $middleware,
        string $path,
        callable|string|array $handler,
    ): void {
        $middlewares = $this->unpackMiddleware($middleware);
        $middlewares[] = $this->createDecoratedMiddleware($handler);

        $handlerWithMiddleware = new class ($middlewares) implements MiddlewareInterface {
            private array $middlewares;

            public function __construct(array $middlewares)
            {
                $this->middlewares = $middlewares;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                foreach ($this->middlewares as $middleware) {
                    $middleware->process($request, $handler);
                }

                return $handler->handle($request);
            }
        };

        $this->map((array) $methods, $path, $handlerWithMiddleware);
    }

    public function group(string $prefix, callable $group): void
    {
        new RouteGroup($prefix, $group, $this);
    }

    public function get(string $path, callable|string|array $handler): void
    {
        $this->map(['GET'], $path, $handler);
    }

    public function post(string $path, callable|string|array $handler): void
    {
        $this->map(['POST'], $path, $handler);
    }

    public function put(string $path, callable|string|array $handler): void
    {
        $this->map(['PUT'], $path, $handler);
    }

    public function patch(string $path, callable|string|array $handler): void
    {
        $this->map(['PATCH'], $path, $handler);
    }

    public function delete(string $path, callable|string|array $handler): void
    {
        $this->map(['DELETE'], $path, $handler);
    }

    public function head(string $path, callable|string|array $handler): void
    {
        $this->map(['HEAD'], $path, $handler);
    }

    public function options(string $path, callable|string|array $handler): void
    {
        $this->map(['OPTIONS'], $path, $handler);
    }

    public function any(string $path, callable|string|array $handler): void
    {
        $this->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $path, $handler);
    }
}
