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

use \BitFrame\Router\RouteGroup;

/**
 * @covers \BitFrame\Router\RouteGroup
 */
class RouteGroupTest extends TestCase
{
    /**
     * Asserts that a route group is created and it registeres routes with collection.
     */
    public function testGroupIsInvokedAndAddsRoutesToCollection()
    {
        $callback   = function() {};
        $collection = $this->createMock('\BitFrame\Router\RouteCollectionInterface');
        $route = $this->createMock('\BitFrame\Router\Route');
        
        $route
            ->expects($this->once())
            ->method('setHost')
            ->with($this->equalTo('example.com'))
            ->will($this->returnSelf());
        
        $route
            ->expects($this->once())
            ->method('setScheme')
            ->with($this->equalTo('https'))
            ->will($this->returnSelf());
        
        $collection
            ->expects($this->at(0))
            ->method('map')
            ->with($this->equalTo('GET'), $this->equalTo('/acme/route'), $this->equalTo($callback))
            ->will($this->returnValue($route));
        
        $collection
            ->expects($this->at(1))
            ->method('map')
            ->with($this->equalTo('POST'), $this->equalTo('/acme/route'), $this->equalTo($callback))
            ->will($this->returnValue($route));
        
        $collection
            ->expects($this->at(2))
            ->method('map')
            ->with($this->equalTo('PUT'), $this->equalTo('/acme/route'), $this->equalTo($callback))
            ->will($this->returnValue($route));
        
        $collection
            ->expects($this->at(3))
            ->method('map')
            ->with($this->equalTo('PATCH'), $this->equalTo('/acme/route'), $this->equalTo($callback))
            ->will($this->returnValue($route));
        
        $collection
            ->expects($this->at(4))
            ->method('map')
            ->with($this->equalTo('DELETE'), $this->equalTo('/acme/route'), $this->equalTo($callback))
            ->will($this->returnValue($route));
        
        $collection
            ->expects($this->at(5))
            ->method('map')
            ->with($this->equalTo('OPTIONS'), $this->equalTo('/acme/route'), $this->equalTo($callback))
            ->will($this->returnValue($route));
        
        $collection
            ->expects($this->at(6))
            ->method('map')
            ->with($this->equalTo('HEAD'), $this->equalTo('/acme/route'), $this->equalTo($callback))
            ->will($this->returnValue($route));
        
        $group = new RouteGroup('/acme', function ($route) use ($callback) {
            $route->get('/route', $callback)->setHost('example.com')->setScheme('https');
            $route->post('/route', $callback);
            $route->put('/route', $callback);
            $route->patch('/route', $callback);
            $route->delete('/route', $callback);
            $route->options('/route', $callback);
            $route->head('/route', $callback);
        }, $collection);
        
        $group();
    }
}