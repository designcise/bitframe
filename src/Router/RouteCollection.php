<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    Phil Bennett <philipobenito@gmail.com>
 * @copyright Copyright (c) 2017 Phil Bennett <philipobenito@gmail.com>
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Router;

use BitFrame\Router\{Route, RouteGroup, RouteCollectionInterface};

/**
 * Stores routes. 
 */
class RouteCollection implements RouteCollectionInterface
{
	use RouteCollectionMapTrait;
	
    /** @var Route[] */
    private $routes = [];
	
    /**
     * {@inheritdoc}
     */
    public function map($method, string $path, $handler): Route
    {
        $path  = sprintf('/%s', ltrim($path, '/'));
		$route = new Route($method, $path, $handler);
        $this->routes[] = $route;
		
		return $route;
    }
	
    /**
     * Add a group of routes to the collection.
     *
     * @param string $prefix
     * @param callable $group
     *
     * @return RouteGroup
     */
    public function group($prefix, callable $group): RouteGroup
    {
        $group = new RouteGroup($prefix, $group, $this);
		
		// process group: __invoke group object callable
		$group();
		
        return $group;
    }
	
	/**
	 * Get stored routes from the collection.
	 *
	 * @return Route[]
	 */
	public function getData(): array 
	{
		return $this->routes;
	}
}