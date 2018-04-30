<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Router;

use \Psr\Http\Server\MiddlewareInterface;

use BitFrame\Router\{Route, RouteGroup, RouteCollectionInterface};

/**
 * Interface defining required router capabilities.
 */
interface RouterInterface extends MiddlewareInterface, RouteCollectionInterface
{
	/**
     * Route map.
	 *
	 * @param array|string $method
	 * @param string $path
	 * @param callable|string|array $handler
	 *
	 * @return Route
     */
	public function map($method, string $path, $handler): Route;
	
	/**
     * Add a group of routes to the collection.
     *
     * @param string $prefix
     * @param callable $group
     *
     * @return RouteGroup
     */
	public function group(string $prefix, callable $group): RouteGroup;
	
	/**
     * Get stored routes.
     *
     * @return Route[]
     */
	public function getRoutes(): array;
}
