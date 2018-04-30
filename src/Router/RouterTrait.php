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

use BitFrame\Router\{Route, RouteGroup, RouteCollectionInterface, RouteCollectionMapTrait};

/**
 * Common methods that all routers can implement.
 */
trait RouterTrait
{
	use RouteCollectionMapTrait;
	
	/** @var RouteCollectionInterface */
	private $routeCollection = null;
	
	/**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouterInterface::map()
     */
	public function map($method, string $path, $handler): Route
	{
		return $this->getRouteCollection()->map($method, $path, $handler);
	}
	
	/**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouterInterface::group()
     */
	public function group(string $prefix, callable $group): RouteGroup
	{
		return $this->getRouteCollection()->group($prefix, $group);
	}
	
	/**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouterInterface::getRoutes()
     */
	public function getRoutes(): array
	{
		return ($this->getRouteCollection())->getData();
	}
	
	/**
     * Get the RouteCollection object.
     *
     * @return RouteCollectionInterface
     */
	public function getRouteCollection(): RouteCollectionInterface
	{
		// use default route collection if one isn't set already
		$this->routeCollection = $this->routeCollection ?? \BitFrame\Factory\ApplicationFactory::createRouteCollection();
		
		return $this->routeCollection;
	}
}