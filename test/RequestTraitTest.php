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

use \BitFrame\Factory\HttpMessageFactory;

/**
 * @covers \BitFrame\Message\RequestTrait
 */
class RequestTraitTest extends TestCase
{
	/** @var \Psr\Http\Message\ServerRequestInterface */
	private $request;
	
    protected function setUp()
    {
		// request created via HttpMessageFactory has RequestTrait added already
		// so we don't need to mock the RequestTrait specially
		$this->request = HttpMessageFactory::createServerRequest('GET', 'https://user:pass@local.example.com:3001/foo/bar/hello-world?key1=val1&key2=val2#quz');
    }
	
	public function testRequestGetEndpoints() 
	{
		$endpoints = $this->request->getEndpoints();
		$expected = ['foo', 'bar', 'hello-world'];
		
		$this->assertSame(array_diff($expected, $endpoints), array_diff($endpoints, $expected));
	}
	
	public function testRequestGetEndpoint() 
	{
		$this->assertSame('foo', $this->request->getEndpoint(1));
		$this->assertSame('bar', $this->request->getEndpoint(2));
		$this->assertSame(null, $this->request->getEndpoint(4));
		$this->assertSame('test', $this->request->getEndpoint(4, 'test'));
	}
	
	public function testRequestHasEndpoint() 
	{
		$request = $this->request;
		
		// single
		$this->assertTrue($request->hasEndpoint('foo/'));
		$this->assertTrue($request->hasEndpoint('bar/'));
		$this->assertTrue($request->hasEndpoint('hello-world/'));
		
		// double
		$this->assertTrue($request->hasEndpoint('foo/bar/'));
		$this->assertTrue($request->hasEndpoint('bar/', 'foo/'));
		$this->assertTrue($request->hasEndpoint('bar/hello-world'));
		$this->assertTrue($request->hasEndpoint('hello-world', 'bar/'));
		$this->assertTrue($request->hasEndpoint(['hello-world', 'quickstart'], 'bar/'));
		
		$this->assertFalse($request->hasEndpoint('foo/hello-world'));
		
		// tripple
		$this->assertTrue($request->hasEndpoint('hello-world', 'foo/bar/'));
		$this->assertTrue($request->hasEndpoint('bar/hello-world', 'foo/'));
		$this->assertTrue($request->hasEndpoint('foo/bar/hello-world', ''));
		$this->assertTrue($request->hasEndpoint('foo/bar/hello-world'));
		
		$this->assertTrue($request->hasEndpoint(['hello-world', 'quickstart'], 'foo/bar/'));
		$this->assertTrue($request->hasEndpoint(['bar/hello-world', 'quickstart'], 'foo/'));
		
		$this->assertTrue($request->hasEndpoint('foo/bar/hello-world', '', true));
		$this->assertTrue($request->hasEndpoint('', 'foo/bar/hello-world', true));
		$this->assertTrue($request->hasEndpoint('bar/hello-world', 'foo/', true));
		
		$this->assertFalse($request->hasEndpoint('foo/bar/', '', true)); // strict (same as isEndpoint())
	}
	
	public function testGetQueryParam() 
	{
		$this->assertSame('val1', $this->request->getQueryParam('key1'));
		$this->assertSame('val2', $this->request->getQueryParam('key2'));
		$this->assertSame(null, $this->request->getQueryParam('nonExistent'));
		$this->assertSame('test', $this->request->getQueryParam('nonExistent', 'test'));
	}
	
	public function testQueryStringParams()
	{
		$querystr = $this->request->getQueryParams();
		
		$this->assertSame('val1', $querystr['key1']);
		$this->assertSame('val2', $querystr['key2']);
	}
	
	public function testIsEndpoint()
	{
		$request = $this->request;
		
		$this->assertTrue($request->isEndpoint('foo/bar/hello-world/'));
		$this->assertTrue($request->isEndpoint(['hello-world/', 'quickstart'], 'foo/bar/'));
		$this->assertTrue($request->isEndpoint(['bar/hello-world/', 'bar/quickstart'], 'foo/'));
		
		$this->assertFalse($request->isEndpoint('foo/bar/'));
		$this->assertFalse($request->isEndpoint('bar/hello-world'));
		$this->assertFalse($request->isEndpoint(['hello-world', 'quickstart'], 'bar/'));
		$this->assertFalse($request->isEndpoint('bar', 'foo/'));
	}
	
	public function testGetCookieParam()
    {
        $request = $this->request->withCookieParams([
            'user' => 'john',
            'id' => '123',
        ]);
		
        $this->assertEquals('john', $request->getCookieParam('user'));
    }
	
	public function testIsXhr()
    {
        $request = $this->request
						->withHeader('Content-Type', 'application/x-www-form-urlencoded')
						->withHeader('X-Requested-With', 'XMLHttpRequest');
		
        $this->assertTrue($request->isXhr());
    }
}
?>