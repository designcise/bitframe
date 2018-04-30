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

use BitFrame\Router\Route;

/**
 * Common implementation of the http router methods.
 */
trait RouteCollectionMapTrait
{
    /**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouteCollectionInterface::map()
     */
    abstract public function map($method, string $path, $handler): Route;
	
    /**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouteCollectionInterface::get()
     */
    public function get(string $path, $handler): Route
    {
        return $this->map('GET', $path, $handler);
    }
	
    /**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouteCollectionInterface::post()
     */
    public function post(string $path, $handler): Route
    {
        return $this->map('POST', $path, $handler);
    }
	
    /**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouteCollectionInterface::put()
     */
    public function put(string $path, $handler): Route
    {
        return $this->map('PUT', $path, $handler);
    }
	
    /**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouteCollectionInterface::patch()
     */
    public function patch(string $path, $handler): Route
    {
        return $this->map('PATCH', $path, $handler);
    }
	
    /**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouteCollectionInterface::delete()
     */
    public function delete(string $path, $handler): Route
    {
        return $this->map('DELETE', $path, $handler);
    }
	
    /**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouteCollectionInterface::head()
     */
    public function head(string $path, $handler): Route
    {
        return $this->map('HEAD', $path, $handler);
    }
	
    /**
     * {@inheritdoc}
	 *
	 * @see \BitFrame\Router\RouteCollectionInterface::options()
     */
    public function options(string $path, $handler): Route
    {
        return $this->map('OPTIONS', $path, $handler);
    }
}