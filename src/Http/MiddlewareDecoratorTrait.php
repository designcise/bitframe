<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

use function is_callable;
use function is_string;
use function is_array;

/**
 * Methods to decorate various middleware types into the compatible
 * `MiddlewareInterface`.
 */
trait MiddlewareDecoratorTrait
{
    /**
     * Push `$middleware` onto the end of middlewares array.
     *
     * @param null|array|string|callable|MiddlewareInterface $middleware
     *
     * @return array
     */
    public function getUnpackedMiddleware(null|array|string|callable|MiddlewareInterface $middleware): array
    {
        if (empty($middleware)) {
            return [];
        }

        if (is_array($middleware) && ! is_callable($middleware, true)) {
            $collection = [];

            foreach ($middleware as $md) {
                $collection = [...$collection, ...$this->getUnpackedMiddleware($md)];
            }

            return $collection;
        }

        return [$this->getDecoratedMiddleware($middleware)];
    }

    /**
     * Decorates a middleware as `MiddlewareInterface`.
     *
     * @param array|string|callable|MiddlewareInterface $middleware
     *
     * @return MiddlewareInterface
     */
    public function getDecoratedMiddleware(array|string|callable|MiddlewareInterface $middleware): MiddlewareInterface
    {
        if (! $middleware instanceof MiddlewareInterface) {
            if (is_callable($middleware)) {
                return $this->getDecoratedCallableMiddleware($middleware);
            }

            if (is_string($middleware)) {
                return new $middleware();
            }
        }

        return $middleware;
    }

    /**
     * @param callable $middleware Can be:
     *     - string callable method (e.g. 'className::method');
     *     - an anonymous function (Closure);
     *     - named function;
     *     - invokable object (a class with `__invoke()`);
     *     - a callable array (e.g. `[object, 'method']` or `['className', 'method']`);
     *
     * @return MiddlewareInterface
     */
    public function getDecoratedCallableMiddleware(callable $middleware): MiddlewareInterface
    {
        return new class ($middleware) implements MiddlewareInterface {
            private $middleware;

            public function __construct(callable $middleware)
            {
                $this->middleware = $middleware;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return ($this->middleware)($request, $handler);
            }
        };
    }
}
