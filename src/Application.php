<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame;

use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use \Fig\Http\Message\StatusCodeInterface;

use BitFrame\Router\{RouterTrait, RouteCollectionInterface};
use BitFrame\Data\{ApplicationData, ApplicationDataTrait};
use BitFrame\Dispatcher\{DispatcherAwareTrait, DispatcherInterface};
use BitFrame\EventManager\EventManagerInterface;

use \InvalidArgumentException;

/**
 * The central point of a BitFrame application which stores routes and
 * shared data, runs middlewares, and helps manage events and endpoints.
 */
class Application implements 
    \ArrayAccess, 
    RouteCollectionInterface, 
    StatusCodeInterface, 
    EventManagerInterface
{
    use ApplicationDataTrait {
        offsetSet as offsetSetDelegate;
        offsetUnset as offsetUnsetDelegate;
    }
    use RouterTrait;
    use DispatcherAwareTrait {
        getRequest as getRequestDelegate;
    }
    
    /** @var ApplicationData */
    private $data;
        
    /** @var array */
    private $config;
    
    /** @var ServerRequestInterface */
    private $originalRequest;
        
    /**
     * @param array $config (optional)
     */
    public function __construct(array $config = [])
    {
        // setup configuration
        $this->config = array_merge([
            // default dispatcher
            DispatcherInterface::class => null,
            
            // default request prototype
            ServerRequestInterface::class => null,
            // default response prototype
            ResponseInterface::class => null,
            
            // default route collection
            RouteCollectionInterface::class => null
        ], $config);
        
        // set defaults
        $this->reset();
        
        // clone request if not null
        $request = $this->config[ServerRequestInterface::class];
        $this->originalRequest = (is_null($request)) ? null : clone $request;
    }
    
    /**
     * Run Application.
     *
     * @param callable|\Psr\Http\Server\MiddlewareInterface|array $middleware (optional)
     * @param ServerRequestInterface|null $request (optional)
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function run($middleware = null, ?ServerRequestInterface $request = null): self 
    {
        $this->request = $request ?? $this->getRequest();
        $dispatcher = $this->getDispatcher();
        
        if($middleware === null) {
            // handle request
            $this->response = $dispatcher->handle($this->getRequest());
        }
        else {
            // nothing to execute? stop early
            if (empty($middleware)) {
                throw new InvalidArgumentException('Can\'t execute, no middleware found');
            }
            
            // 1: set new dispatcher as active + backup old
            $oldDispatcher = $dispatcher;
            $dispatcher = clone $dispatcher;
            $this->setDispatcher($dispatcher);
            
            // 2: reset pending + processed middleware lists
            $dispatcher->clear();
            
            // 3: push middleware to end of queue
            $dispatcher->addMiddleware($middleware);

            // 4: execute middleware(s)
            $this->response = $dispatcher->handle($this->getRequest());
            
            // 5: ensure request is shared between old/new dispatchers as it may have been modified
            $oldDispatcher->setRequest($dispatcher->getRequest());
            
            // 6: set old dispatcher as active
            $this->setDispatcher($oldDispatcher);
        }
        
        // update request internally as it may have been modified since we sent it through
        $this->setRequest($dispatcher->getRequest());
        
        return $this;
    }
        
    /**
     * Reset application to defaults.
     */
    public function reset()
    {
        // request/response
        $this->request = $this->config[ServerRequestInterface::class];
        $this->response = $this->config[ResponseInterface::class];
        
        // middleware dispatcher
        $this->dispatcher = $this->config[DispatcherInterface::class];
        
        // routes collection
        $this->routeCollection = $this->config[RouteCollectionInterface::class];
        
        // application data
        $this->data = new ApplicationData();
    }
        
    /**
     * {@inheritdoc}
     *
     * @see ArrayAccess::offsetSet()
     * @see ApplicationDataTrait::offsetSet()
     */
    public function offsetSet($key, $value)
    {
        $this->validateKey($key);
        
        $this->offsetSetDelegate($key, $value);
    }
        
    /**
     * {@inheritdoc}
     *
     * @see ArrayAccess::offsetUnset()
     * @see ApplicationDataTrait::offsetUnset()
     */
    public function offsetUnset($key)
    {
        $this->validateKey($key);
        
        $this->offsetUnsetDelegate($key);
    }
        
    /**
     * {@inheritdoc}
     */
    public function getData(): ApplicationData
    {
        return $this->data;
    }
    
    /**
     * Get Application data as array.
     *
     * @return mixed[]
     */
    public function getDataArray(): array
    {
        return $this->data->getData();
    }
        
    /**
     * Get Http request object.
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        $request = $this->getRequestDelegate();
        
        // clone $request if lazy instantiating
        $this->originalRequest = ($this->originalRequest) ?? clone $request;
        
        // share routes + application data via the http request object
        return $request
            ->withAttribute(ApplicationData::class, $this->data)
            ->withAttribute(RouteCollectionInterface::class, $this->getRouteCollection());
    }
        
    /**
     * Get original Http request object as received by 
     * your web server.
     *
     * @return ServerRequestInterface
     */
    public function getOriginalRequest(): ServerRequestInterface
    {
        // clone request, if not yet instantiated (may happen when lazy instantiating)
        $this->originalRequest = ($this->originalRequest) ?? clone $this->getRequestDelegate();
        
        return $this->originalRequest;
    }
        
    /**
     * Checks for reserved keys.
     *
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    private function validateKey($key)
    {
        if (
            $key === ApplicationData::class || 
            $key === RouteCollectionInterface::class
        ) {
            throw new InvalidArgumentException((sprintf(
                '"%s" is a reserved key and can therefore not be used',
                $key
            )));
        }
    }
}
