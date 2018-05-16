<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Factory;

use \Psr\Http\Message\ResponseInterface;

use BitFrame\Factory\{EventManagerFactoryInterface, DispatcherFactoryInterface, RouteCollectionFactoryInterface};
use BitFrame\EventManager\EventManagerInterface;
use BitFrame\Dispatcher\DispatcherInterface;
use BitFrame\Router\RouteCollectionInterface;

/**
 * Factory methods for application components.
 */
class ApplicationFactory
{
    /** @var EventManagerFactoryInterface */
    private static $eventManagerFactory;
    
    /** @var DispatcherFactoryInterface */
    private static $dispatcherFactory;
    
    /** @var RouteCollectionFactoryInterface */
    private static $routeCollectionFactory;
    
    /**
     * Set a custom EventManager factory.
     *
     * @param EventManagerFactoryInterface $eventManagerFactory
     */
    public static function setEventManagerFactory(EventManagerFactoryInterface $eventManagerFactory): self
    {
        self::$eventManagerFactory = $eventManagerFactory;
        
        return new static;
    }
    
    /**
     * Set a custom Dispatcher factory.
     *
     * @param DispatcherInterface $dispatcherFactory
     */
    public static function setDispatcherFactory(DispatcherFactoryInterface $dispatcherFactory): self
    {
        self::$dispatcherFactory = $dispatcherFactory;
        
        return new static;
    }
    
    /**
     * Set a custom RouterCollection factory.
     *
     * @param RouteCollectionFactoryInterface $routeCollectionFactory
     */
    public static function setRouteCollectionFactory(RouteCollectionFactoryInterface $routeCollectionFactory): self
    {
        self::$routeCollectionFactory = $routeCollectionFactory;
        
        return new static;
    }
    
    /**
     * Create an EventManagerInterface instance.
     *
     * @return EventManagerInterface
     */
    public static function createEventManager(): EventManagerInterface
    {
        if (self::$eventManagerFactory === null) {
            self::$eventManagerFactory = new \BitFrame\Factory\EventManagerFactory();
        }

        return self::$eventManagerFactory->createEventManager();
    }
    
    /**
     * Create a DispatcherInterface instance.
     *
     * @param ResponseInterface $response
     *
     * @return DispatcherInterface
     */
    public static function createDispatcher(ResponseInterface $response): DispatcherInterface
    {
        if (self::$dispatcherFactory === null) {
            self::$dispatcherFactory = new \BitFrame\Factory\DispatcherFactory();
        }

        return self::$dispatcherFactory->createDispatcher($response);
    }
    
    /**
     * Create a RouteCollectionInterface instance.
     *
     * @return RouteCollectionInterface
     */
    public static function createRouteCollection(): RouteCollectionInterface
    {
        if (self::$routeCollectionFactory === null) {
            self::$routeCollectionFactory = new \BitFrame\Factory\RouteCollectionFactory();
        }

        return self::$routeCollectionFactory->createRouteCollection();
    }
}