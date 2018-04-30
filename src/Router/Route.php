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

use \Psr\Http\Server\MiddlewareInterface;

use BitFrame\Router\RouteGroup;

/**
 * Holds data about a route including the methods, path,
 * callback and group it belongs to, if any. 
 */
class Route
{
	use RouteConditionTrait;
	
    /** @var string|callable */
    private $callable;
	
    /** @var RouteGroup */
    private $group;
	
    /** @var string[] */
    private $methods;
	
    /** @var string */
    private $path;
	
	/**
     * @param array|string $methods
	 * @param string $path
	 * @param callable|string|array $handler
     */
	public function __construct($methods, string $path, $handler)
    {
        $this->setMethods((array) $methods);
		$this->setPath($path);
		$this->setCallable($handler);
		
		$this->group = null;
    }
	
    /**
     * Get the callable.
     *
     * @return callable|MiddlewareInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getCallable()
    {
        $callable = $this->callable;
		
		// case 0: router is a middleware (because router itself can be treated as a middleware)
		if (! $callable instanceof MiddlewareInterface) {
			// case 1: class::method
			if (is_string($callable) && strpos($callable, '::') !== false) {
				$callable = explode('::', $callable);
			}
			// case 2: [object, method] 
			if (is_array($callable) && isset($callable[0]) && is_object($callable[0])) {
				$callable = [$callable[0], $callable[1]];
			}
			// case 3: [className, method]
			if (is_array($callable) && isset($callable[0]) && is_string($callable[0])) {
				$class = new $callable[0];
				$callable = [$class, $callable[1]];
			}
			// case 4: not supported
			if (! is_callable($callable)) {
				throw new \InvalidArgumentException('Could not resolve a callable for this route');
			}
		}
        return $callable;
    }
	
    /**
     * Set the callable.
     *
     * @param string|callable $callable
     *
     * @return Route
     */
    public function setCallable($callable): Route
    {
        $this->callable = $callable;
		
        return $this;
    }
	
    /**
     * Get the parent group.
     *
     * @return RouteGroup
     */
    public function getParentGroup(): RouteGroup
    {
        return $this->group;
    }
	
    /**
     * Set the parent group.
     *
     * @param RouteGroup $group
     *
     * @return Route
     */
    public function setParentGroup(RouteGroup $group): Route
    {
        $this->group = $group;
		
        return $this;
    }
	
    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
	
    /**
     * Set the path.
     *
     * @param string $path
     *
     * @return Route
     */
    public function setPath(string $path): Route
    {
        $this->path = $path;
		
        return $this;
    }
	
    /**
     * Get the methods.
     *
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
	
    /**
     * Get the methods.
     *
     * @param string[] $methods
     *
     * @return Route
     */
    public function setMethods(array $methods): Route
    {
        $this->methods = $methods;
		
        return $this;
    }
}