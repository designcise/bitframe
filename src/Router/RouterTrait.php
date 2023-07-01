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

use Psr\Http\Server\MiddlewareInterface;

trait RouterTrait
{
    /**
     * Add a route to the map.
     *
     * @param array|string $methods
     * @param string $path
     * @param callable|string|array|MiddlewareInterface $handler
     */
    abstract public function map(
        array|string $methods,
        string $path,
        callable|string|array|MiddlewareInterface $handler,
    );
}
