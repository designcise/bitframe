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

use BitFrame\Dispatcher\DispatcherInterface;
use BitFrame\EventManager\{EventManagerInterface, EventManagerAwareTrait};
use BitFrame\Factory\HttpMessageFactory;

/**
 * This trait exposes the Dispatcher to any 'aware' class.
 */
trait DispatcherAwareTrait
{
	use EventManagerAwareTrait;
	
	/** @var DispatcherInterface */
    private $dispatcher;

    /** @var ResponseInterface */
    private $response;
		
	/** @var ServerRequestInterface */
    private $request;
	
	/**
     * {@inheritdoc}
	 *
	 * @see DispatcherInterface::addMiddleware()
     */
    public function addMiddleware($middleware): self
    {
		$this->getDispatcher()->addMiddleware($middleware);
		
		return $this;
    }
	
	/**
     * Set middleware dispatcher object.
	 *
	 * @param DispatcherInterface $dispatcher
	 *
	 * @return $this;
     */
	public function setDispatcher(DispatcherInterface $dispatcher): self
	{
		$this->dispatcher = $dispatcher;
		
		return $this;
	}
		
	/**
	 * Set Request object.
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
		$this->request = $this->request ?? HttpMessageFactory::createServerRequestFromArray($_SERVER);
		
        return $this->request;
    }
	
	/**
     * Get Http Response object.
	 *
	 * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
		$this->response = $this->response ?? HttpMessageFactory::createResponse();
		
        return $this->response;
    }
	
	/**
     * Get middleware dispatcher object.
	 *
	 * @return DispatcherInterface
     */
	public function getDispatcher(): DispatcherInterface
	{
		$this->dispatcher = $this->dispatcher ?? \BitFrame\Factory\ApplicationFactory::createDispatcher($this->getResponse());
		
		return $this->dispatcher;
	}
	
	/**
     * Get the Event Manager object.
	 *
	 * @return EventManagerInterface
     */
	public function getEventManager(): EventManagerInterface
	{
		$this->eventManager = $this->eventManager ?? $this->getDispatcher()->getEventManager();
		
		return $this->eventManager;
	}
}