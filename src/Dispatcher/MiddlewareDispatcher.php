<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Dispatcher;

use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use \Psr\Http\Server\MiddlewareInterface;

use BitFrame\EventManager\{Event, EventManagerInterface, EventManagerAwareTrait};
use BitFrame\Exception\InvalidMiddlewareException;

/**
 * Queues and runs middleware to create an http response based 
 * on the http request.
 */
class MiddlewareDispatcher implements DispatcherInterface, EventManagerInterface
{
	use EventManagerAwareTrait;
	
	/** @var string */
    public const EVENT_BEFORE_DISPATCH = 'before.dispatch';
	
	/** @var string */
    public const EVENT_AFTER_DISPATCH = 'after.dispatch';
	
	/** @var string */
    public const EVENT_DONE_DISPATCH = 'done.dispatch';
	
	/** @var array */
    private $pendingMiddleware = [];
	
	/** @var array */
	private $processedMiddleware = [];
	
	/** @var bool */
	private $running = false;
	
	/** @var bool */
	private $immediatelyInvoked = false;
	
    /** @var null|ServerRequestInterface */
	private $request = null;
	
    /** @var null|ResponseInterface */
    private $response;
	
	/** @var null|EventManagerInterface */
	private $eventManager = null;
	
	/**
     * @param ResponseInterface|null $response (optional)
     */
    public function __construct(?ResponseInterface $response = null)
    {
		$this->response = $response;
    }
	
    /**
     * {@inheritdoc}
	 * @param bool $addToFront (optional)
	 *
     * @return $this
     */
    public function addMiddleware($middleware, $addToFront = false): self
	{
		if (! empty($middleware)) {
			// case 1: single middleware?
			// case 2: in the format [Object, 'method']?
			if (! is_array($middleware) || (is_callable($middleware, true) && method_exists($middleware[0], $middleware[1]))) {
				$middleware = [$middleware];
			}

			$chain =& $this->pendingMiddleware;
			// add the new middleware at the front or end of the middleware queue
			// note: using array_merge means:
				// 1: conflicting numeric keys don't overwrite but are appended;
				// 2: numeric keys in array are renumbered starting from 0
			$chain = ($addToFront) ? array_merge($middleware, $chain) : array_merge($chain, $middleware);
		}
		
        return $this;
    }
	
	/**
     * Add a middleware to front of queue.
     *
	 * @param callable|MiddlewareInterface|array $middleware
	 *
     * @return $this
     */
	public function prependMiddleware($middleware): self
	{
		return $this->addMiddleware($middleware, true);
    }
	
    /**
     * {@inheritdoc}
	 *
     * @return $this
     */
	public function clear(): self
	{
		$this->pendingMiddleware = [];
		$this->processedMiddleware = [];
		
		return $this;
	}
		
    /**
     * Process request.
     *
	 * @param ServerRequestInterface $request
	 *
     * @return ResponseInterface
	 *
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException
	 * @throws \BadFunctionCallException
	 * @throws \BitFrame\Exception\InvalidMiddlewareException
	 * @throws \BitFrame\Exception\HttpException
	 * @throws \BitFrame\Exception\BadRequestException
	 * @throws \BitFrame\Exception\UnauthorizedException
	 * @throws \BitFrame\Exception\ForbiddenException
	 * @throws \BitFrame\Exception\RouteNotFoundException
	 * @throws \BitFrame\Exception\MethodNotAllowedException
	 * @throws \BitFrame\Exception\InternalErrorException
	 * @throws \BitFrame\Exception\NotImplementedException
	 * @throws \BitFrame\Exception\NotImplementedException
	 *
	 * @triggers after.dispatch  After a middleware has been processed
	 * @triggers before.dispatch Before a middleware is processed
	 * @triggers done.dispatch   When a middleware has been processed
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
		if(! $this->running && ! $this->hasMiddleware()) {
			throw new \InvalidArgumentException('Can\'t run, no middleware found.');
		}
		
		$this->running = true;
		
		// process response before first or after last middleware has been processed
		$this->validateHttpResponse($request, $this->getResponse());
		
		// trigger 'after' event
		if (($lastMiddleware = end($this->processedMiddleware) ?: null) !== null) {
			$this->triggerEvent(self::EVENT_AFTER_DISPATCH, $lastMiddleware);
		}
		
		$middleware = array_shift($this->pendingMiddleware);

		// middleware ready to be processed?
		if (isset($middleware)) {
			// get middleware instance; must be callable or implement MiddlewareInterface
			// only proceed to check for class if a function by specified name does not exist!
			if (is_string($middleware) && ! function_exists($middleware)) {
				// middleware class does not exist!
				if (! class_exists($middleware)) {
					throw new InvalidMiddlewareException(sprintf(
						'Unable to create middleware "%s"; not a valid class or service name',
						$middleware
					));
				}

				// middleware class name provided? create new instance of the middleware
				// instance will be validated further in the code that follows
				$middleware = new $middleware();
			}

			// middleware does not implement PSR-15 middleware interface and is not callable?
			if (! ($isMiddleware = ($middleware instanceof MiddlewareInterface)) && ! is_callable($middleware)) {
				// class instance is not PSR-15 & PSR-7 compatible
				if (is_object($middleware)) {
					throw new InvalidMiddlewareException(sprintf(
						'Middleware of class "%s" is invalid; neither invokable nor %s',
						get_class($middleware),
						MiddlewareInterface::class
					));
				} else {
					// middleware is not a function
					throw new \BadFunctionCallException('Middleware is not callable');
				}
			}
			
			// trigger 'before' event
			$this->triggerEvent(self::EVENT_BEFORE_DISPATCH, $middleware);

			// push at end of array
			$this->processedMiddleware[] = $middleware;
			
			// case 1: implements MiddlewareInterface
			$response = ($isMiddleware) ? $middleware->process($request, $this) : 
			(
				(is_array($middleware)) ? 
				// case 2.1: in the format [Object, 'method']: ResponseInterface
				$middleware[0]->{$middleware[1]}($request, $this) : 
				// case 2.2: callable function($request, $response, $next): ResponseInterface
				$middleware($request, $this->getResponse(), [$this, 'handle'])
			);
			
			// @internal to terminate early, a middleware must only return $response (and not $next(..) or $handler->handle(..))
		
			// process response after all middleware has been processed
			$this->validateHttpResponse($request, $response);
		
			$this->setResponse($response);
		} else {
			// no executable middleware remaining? clean up!
			$this->processedMiddleware = [];
			
			// update request internally as it may have been modified being passed through all the middlewares
			$this->setRequest($request);
        }
		
		if ($lastMiddleware !== null) {
			// trigger 'done' event
			$this->triggerEvent(self::EVENT_DONE_DISPATCH, $lastMiddleware);
		}
		
		$this->running = false;
		
		return $this->getResponse();
    }
	
	/**
     * {@inheritdoc}
     */
	public function isRunning(): bool
	{
		return $this->running;
	}
	
	/**
     * {@inheritdoc}
     */
	public function hasMiddleware(): bool
	{
		return (! empty($this->pendingMiddleware));
	}
	
    /**
     * {@inheritdoc}
     */
    public function getPendingMiddleware(): array
	{
        return $this->pendingMiddleware;
    }
	
	/**
     * {@inheritdoc}
     */
	public function getProcessedMiddleware(): array
	{
		return $this->processedMiddleware;
	}
	
	/**
	 * Set Http Request object.
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return $this
	 */
	public function setRequest(ServerRequestInterface $request): self
	{
		$this->request = $request;
		
		return $this;
	}
	
	/**
     * Get Http Request object.
	 *
	 * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
		$this->request = $this->request ?? \BitFrame\Factory\HttpMessageFactory::createServerRequestFromArray($_SERVER);
		
        return $this->request;
    }
		
	/**
     * Set Http Response object.
	 *
	 * @param ResponseInterface $response
	 *
	 * @return $this
     */
    public function setResponse(ResponseInterface $response): self
    {
		$this->response = $response;
		
		return $this;
    }
	
	/**
     * Get Http Response object.
	 *
	 * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
		$this->response = $this->response ?? \BitFrame\Factory\HttpMessageFactory::createResponse();
		
        return $this->response;
    }
	
	/**
     * Get the Event Manager object.
	 *
	 * @return EventManagerInterface
     */
	public function getEventManager(): EventManagerInterface
	{
		$this->eventManager = $this->eventManager ?? \BitFrame\Factory\ApplicationFactory::createEventManager();
		
		return $this->eventManager;
	}
	
	/**
	 * Trigger an event.
	 *
	 * @param string $evtName
	 * @param callable|MiddlewareInterface|array $middleware
	 */
	private function triggerEvent(string $evtName, $middleware)
	{
		$this->trigger(new Event($evtName, ((is_string($middleware)) ? $middleware : get_class((is_array($middleware)) ? $middleware[0] : $middleware)), [
			'response' => $this->getResponse()
		]));
	}
	
	/**
	 * Process response to see if an HTTP exception needs to be thrown.
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 *
	 * @throws \UnexpectedValueException
	 * @throws \BitFrame\Exception\HttpException
	 * @throws \BitFrame\Exception\BadRequestException
	 * @throws \BitFrame\Exception\UnauthorizedException
	 * @throws \BitFrame\Exception\ForbiddenException
	 * @throws \BitFrame\Exception\RouteNotFoundException
	 * @throws \BitFrame\Exception\MethodNotAllowedException
	 * @throws \BitFrame\Exception\InternalErrorException
	 * @throws \BitFrame\Exception\NotImplementedException
	 * @throws \BitFrame\Exception\NotImplementedException
	 */
	private function validateHttpResponse(ServerRequestInterface $request, ResponseInterface $response)
	{
		// invalid response received?
		if (! ($response instanceof ResponseInterface)) {
			throw new \UnexpectedValueException(sprintf(
				'Application did not return a valid "%s" instance',
				ResponseInterface::class
			));
		}
		
		// terminate request/response handling?
		if (($statusCode = $response->getStatusCode()) >= 300) {
			// allow 3xx (redirect responses) iff Location header is present!
			if (($statusCode < 400 && ! $response->hasHeader('Location')) || $statusCode >= 600) {
				// if http status code 3xx or >= 6xx
				throw new \BitFrame\Exception\HttpException(
					 "Invalid HTTP status code '$statusCode'; must be 4xx or 5xx"
				);
			} 
			
			if ($statusCode >= 400 && $statusCode < 600) {
				// throw exception
				switch($statusCode) {
					case 400:
						throw new \BitFrame\Exception\BadRequestException($response->getReasonPhrase());
					break;
					
					case 401:
						throw new \BitFrame\Exception\UnauthorizedException($response->getReasonPhrase());
					break;
					
					case 403:
						throw new \BitFrame\Exception\ForbiddenException($response->getReasonPhrase());
					break;
					
					case 404:
						throw new \BitFrame\Exception\RouteNotFoundException($request->getUri()->getPath());
					break;
						
					case 405:
						throw new \BitFrame\Exception\MethodNotAllowedException($request->getMethod());
					break;
					
					case 500:
						throw new \BitFrame\Exception\InternalErrorException($response->getReasonPhrase());
					break;
					
					case 501:
						throw new \BitFrame\Exception\NotImplementedException($request->getMethod());
					break;
						
					case 503:
						throw new \BitFrame\Exception\ServiceUnavailableException($response->getReasonPhrase());
					break;
						
					default:
						throw new \BitFrame\Exception\HttpException($response->getReasonPhrase(), $statusCode);
					break;
				}
			}
            
        }
	}
}