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

use \BitFrame\Router\RouteCollection;

/**
 * @covers \BitFrame\Router\RouteCollection
 */
class RouteCollectionTest extends TestCase
{
    /**
     * Asserts that the collection can map and return a route object.
     *
     * @dataProvider httpMethods
     */
    public function testCollectionMapsAndReturnsRoute($method)
    {
        $collection = new RouteCollection;
        $path = '/something';
        $callable = function() {};
        
        $route = $collection->map($method, $path, $callable);
        $this->assertInstanceOf('\BitFrame\Router\Route', $route);
        $this->assertSame([$method], $route->getMethods());
        $this->assertSame($path, $route->getPath());
        $this->assertSame($callable, $route->getCallable());
    }
    
    public function httpMethods()
    {
        return [['get'], ['post'], ['put'], ['patch'], ['delete'], ['head'], ['options']];
    }
    
    /**
     * Asserts that the collection can map and return a route group object.
     */
    public function testCollectionMapsAndReturnsGroup()
    {
        $collection = new RouteCollection;
        $prefix = '/something';
        $callable = function() {};
        
        $group = $collection->group($prefix, $callable);
        $this->assertInstanceOf('\BitFrame\Router\RouteGroup', $group);
    }
}