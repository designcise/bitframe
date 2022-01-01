<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Router;

use Psr\Http\Server\MiddlewareInterface;

use function ltrim;
use function substr;

/**
 * Group multiple routes together under the same prefix.
 */
class RouteGroup extends AbstractRouter
{
    /**
     * @param string $prefix
     * @param callable $handler
     * @param AbstractRouter $routeMapper
     */
    public function __construct(
        protected string $prefix,
        protected $handler,
        protected AbstractRouter $routeMapper
    ) {
        $this->prefix = '/' . ltrim($prefix, '/');
        ($this->handler)($this);
    }

    /**
     * {@inheritdoc}
     */
    public function map(
        array|string $methods,
        string $path,
        callable|string|array|MiddlewareInterface $handler,
    ): void {
        if ($path === '' || $path === '/') {
            $path = '';
        } else {
            $path = ((substr($this->prefix, -1) === '/') ? '' : '/')
                . ltrim($path, '/');
        }

        $this->routeMapper
            ->map($methods, $this->prefix . $path, $handler);
    }
}
