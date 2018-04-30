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

namespace BitFrame\Test;

use \PHPUnit\Framework\TestCase;

use \BitFrame\Router\Route;
use \BitFrame\Test\Asset\Controller;

/**
 * @covers \BitFrame\Router\Route
 */
class RouteTest extends TestCase
{
    /**
     * Asserts that the route can set and resolve an invokable class callable.
     */
    public function testRouteSetsAndResolvesInvokableClassCallable()
    {
        $route = new Route('GET', '/', new Controller);
        $this->assertTrue(is_callable($route->getCallable()));
    }
	
    /**
     * Asserts that the route can set and resolve a class method callable.
     */
    public function testRouteSetsAndResolvesClassMethodCallable()
    {
        $route = new Route('GET', '/', [new Controller, 'action']);
        $this->assertTrue(is_callable($route->getCallable()));
    }
	
    /**
     * Asserts that the route can set and resolve a named function callable.
     */
    public function testRouteSetsAndResolvesNamedFunctionCallable()
    {
        $route = new Route('GET', '/', 'BitFrame\Test\Asset\namedFunctionCallable');
        $this->assertTrue(is_callable($route->getCallable()));
    }
	
    /**
     * Asserts that the route can set and resolve a class method callable without the container.
     */
    public function testRouteSetsAndResolvesClassMethodCallableAsStringWithoutContainer()
    {
        $route = new Route('GET', '/', '\BitFrame\Test\Asset\Controller::action');
        $callable = $route->getCallable();
        $this->assertTrue(is_callable($callable));
        $this->assertTrue(is_array($callable));
        $this->assertCount(2, $callable);
        $this->assertInstanceOf('\BitFrame\Test\Asset\Controller', $callable[0]);
        $this->assertEquals('action', $callable[1]);
    }
    /**
     * Asserts that the route throws an exception when trying to set and resolve a non callable.
     */
    public function testRouteThrowsExceptionWhenSettingAndResolvingNonCallable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $callable = new \stdClass;
        $route = new Route([], '', $callable);
        $route->getCallable();
    }
    /**
     * Asserts that the route can set and get all properties.
     */
    public function testRouteCanSetAndGetAllProperties()
    {
        $route = new Route([], '', function() {});
		
        $group = $this->getMockBuilder('\BitFrame\Router\RouteGroup')->disableOriginalConstructor()->getMock();
        $this->assertSame($group, $route->setParentGroup($group)->getParentGroup());
		
        $path = '/something';
        $this->assertSame($path, $route->setPath($path)->getPath());
		
        $methods = ['get', 'post'];
        $this->assertSame($methods, $route->setMethods($methods)->getMethods());
		
        $scheme = 'http';
        $this->assertSame($scheme, $route->setScheme($scheme)->getScheme());
		
        $host = 'example.com';
        $this->assertSame($host, $route->setHost($host)->getHost());
    }
}