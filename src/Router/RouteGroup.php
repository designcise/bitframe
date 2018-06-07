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

use BitFrame\Router\{Route, RouteCollectionInterface};

/**
 * Group multiple routes together under the same prefix.
 */
class RouteGroup implements RouteCollectionInterface
{
    use RouteCollectionMapTrait;
    use RouteConditionTrait;
    
    /** @var callable */
    protected $callback;
    
    /** @var RouteCollectionInterface */
    protected $collection;
    
    /** @var string */
    protected $prefix;
    
    /**
     * @param string $prefix
     * @param callable $callback
     * @param RouteCollectionInterface $collection
     */
    public function __construct(
        string $prefix, 
        callable $callback, 
        RouteCollectionInterface $collection
    ) {
        $this->callback   = $callback;
        $this->collection = $collection;
        $this->prefix     = sprintf('/%s', ltrim($prefix, '/'));
    }
    
    /**
     * Process the group and ensure routes are added to the collection.
     */
    public function __invoke()
    {
        call_user_func_array($this->callback->bindTo($this), [$this]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function map($method, string $path, $handler): Route
    {
        $path  = ($path === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($path, '/'));
        $route = $this->collection->map($method, $path, $handler);
        $route->setParentGroup($this);
        if ($host = $this->getHost()) {
            $route->setHost($host);
        }
        if ($scheme = $this->getScheme()) {
            $route->setScheme($scheme);
        }
        
        return $route;
    }
}