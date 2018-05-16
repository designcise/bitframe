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
 * Representation of a route collection.
 */
interface RouteCollectionInterface
{
    /**
     * Add a route to the map.
     *
     * @param array|string $method
     * @param string $path
     * @param callable|string|array $handler
     *
     * @return Route
     */
    public function map($method, string $path, $handler): Route;
    
    /**
     * Add a route that responds to GET HTTP method.
     *
     * @param string $path
     * @param callable|string|array $handler
     *
     * @return Route
     */
    public function get(string $path, $handler): Route;
    
    /**
     * Add a route that responds to POST HTTP method.
     *
     * @param string $path
     * @param callable|string|array $handler
     *
     * @return Route
     */
    public function post(string $path, $handler): Route;
    
    /**
     * Add a route that responds to PUT HTTP method.
     *
     * @param string $path
     * @param callable|string|array $handler
     *
     * @return Route
     */
    public function put(string $path, $handler): Route;
    
    /**
     * Add a route that responds to PATCH HTTP method.
     *
     * @param string $path
     * @param callable|string|array $handler
     *
     * @return Route
     */
    public function patch(string $path, $handler): Route;
    
    /**
     * Add a route that responds to DELETE HTTP method.
     *
     * @param string $path
     * @param callable|string|array $handler
     *
     * @return Route
     */
    public function delete(string $path, $handler): Route;
    
    /**
     * Add a route that responds to HEAD HTTP method.
     *
     * @param string $path
     * @param callable|string|array $handler
     *
     * @return Route
     */
    public function head(string $path, $handler): Route;
    
    /**
     * Add a route that responds to OPTIONS HTTP method.
     *
     * @param string $path
     * @param callable|string|array $handler
     *
     * @return Route
     */
    public function options(string $path, $handler): Route;
}