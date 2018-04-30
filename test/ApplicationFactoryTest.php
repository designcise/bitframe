<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Test;

use \PHPUnit\Framework\TestCase;

use \BitFrame\Factory\{ApplicationFactory, HttpMessageFactory, EventManagerFactoryInterface, DispatcherFactoryInterface, RouteCollectionFactoryInterface};

use \BitFrame\Application;
use \BitFrame\Router\Route;
use \BitFrame\EventManager\EventManagerInterface;
use \BitFrame\Dispatcher\MiddlewareDispatcher;

/**
 * @covers \BitFrame\Factory\ApplicationFactory
 */
class ApplicationFactoryTest extends TestCase
{
	
	public function testCustomEventManagerFactory()
	{
		$mock = $this->getMockBuilder('\BitFrame\EventManager\EventManagerInterface')
			->setMockClassName('foo')
			->getMock();
		
		$factoryMock = $this->createMock(\BitFrame\Factory\EventManagerFactoryInterface::class);
		$factoryMock
			->method('createEventManager')
			->willReturn($mock);
		
		ApplicationFactory::setEventManagerFactory($factoryMock);
		
		$dispatcher = new MiddlewareDispatcher();
		
		// after
		$this->assertInstanceOf('foo', $dispatcher->getEventManager());
		
		// reset
		ApplicationFactory::setEventManagerFactory(new \BitFrame\Factory\EventManagerFactory());
	}
	
	public function testDefaultEventManagerFactory() 
	{
		$dispatcher = new MiddlewareDispatcher();
		
		$this->assertInstanceOf('\BitFrame\EventManager\EventManager', $dispatcher->getEventManager());
	}
	
	public function testCustomDispatcherFactory()
	{
		$mock = $this->getMockBuilder('\BitFrame\Dispatcher\DispatcherInterface')
			->setMockClassName('fooz')
			->getMock();
		
		
		$app = new Application();
		$response = $app->getResponse();
		
		$factoryMock = $this->createMock(\BitFrame\Factory\DispatcherFactoryInterface::class);
		$factoryMock
			->method('createDispatcher')
			->with($response)
			->willReturn($mock);
		
		ApplicationFactory::setDispatcherFactory($factoryMock);
		
		// after
		$this->assertInstanceOf('fooz', $app->getDispatcher());
		
		// reset
		ApplicationFactory::setDispatcherFactory(new \BitFrame\Factory\DispatcherFactory());
	}
	
	public function testDefaultDispatcherFactory()
	{
		$app = new Application();
		
		$this->assertInstanceOf('\BitFrame\Dispatcher\DispatcherInterface', $app->getDispatcher());
	}
	
	public function testCustomRouteCollectionFactory()
	{
		$mock = $this->getMockBuilder('\BitFrame\Router\RouteCollectionInterface')
			->setMockClassName('bar')
			->getMock();
		
		$factoryMock = $this->createMock(\BitFrame\Factory\RouteCollectionFactoryInterface::class);
		$factoryMock
			->method('createRouteCollection')
			->willReturn($mock);
		
		ApplicationFactory::setRouteCollectionFactory($factoryMock);
		
		$app = new Application();
		
		// after
		$this->assertInstanceOf('bar', $app->getRouteCollection());
		
		// reset
		ApplicationFactory::setRouteCollectionFactory(new \BitFrame\Factory\RouteCollectionFactory());
	}
	
	public function testDefaultRouteCollectionFactory()
	{
		$app = new Application();
		
		$this->assertInstanceOf('\BitFrame\Router\RouteCollectionInterface', $app->getRouteCollection());
	}
}