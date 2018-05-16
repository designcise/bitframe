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

use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

use \BitFrame\Factory\HttpMessageFactory;
use \BitFrame\EventManager\Event;
use \BitFrame\Test\Asset\InteropMiddleware;

/**
 * @covers \BitFrame\Dispatcher\MiddlewareDispatcher
 */
class MiddlewareDispatcherTest extends TestCase
{
    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $request;
    
    /** @var \BitFrame\Dispatcher\MiddlewareDispatcher */
    private $dispatcher;
    
    /** @var \BitFrame\Test\Asset\InteropMiddleware */
    private $noopMiddleware;
    
    protected function setUp()
    {
        $this->request = HttpMessageFactory::createServerRequest();
        $this->dispatcher = new \BitFrame\Dispatcher\MiddlewareDispatcher();
        $this->noopMiddleware = new InteropMiddleware();
    }
    
    public function testHandleAddMiddleware() 
    {
        $dispatcher = new \BitFrame\Dispatcher\MiddlewareDispatcher();
        
        $eventManager = $this->getMockBuilder(\BitFrame\EventManager\EventManager::class)->setMethods(['getListeners'])->getMock();
        
        $dispatcher->setEventManager($eventManager);
        
        // 1 middleware x 3 events = 3 times 'getListeners' would be called
        // call stack: MiddlewareDispatcher::triggerEvent():private > trigger() > getListeners()
        $eventManager->expects($this->exactly(3))->method('getListeners');
        
        $dispatcher
            ->attach('before.middleware', function ($event) {
                $this->assertInstanceOf(Event::class, $event);

                $data = $event->getParams();

                $this->assertInstanceOf(ResponseInterface::class, $data['response']);
            })
            ->attach('after.middleware', function ($event) {
                $this->assertInstanceOf(Event::class, $event);

                $data = $event->getParams();

                $this->assertInstanceOf(ResponseInterface::class, $data['response']);
            })
            ->attach('done.middleware', function ($event) {
                $this->assertInstanceOf(Event::class, $event);

                $data = $event->getParams();

                $this->assertInstanceOf(ResponseInterface::class, $data['response']);
            })
            // use the 'run' method instead of the PSR-15 'process'
            ->addMiddleware([$this->noopMiddleware, 'run']);
        
        $response = $dispatcher->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
    
    public function testAddExactlyTwoMiddlewareWhereFirstIsAnObjectAndSecondIsString() 
    {
        // need to make this check as it can be mistaken for a callable in the format:
        // [Object, 'method']
        
        $dispatcher = new \BitFrame\Dispatcher\MiddlewareDispatcher();
        
        $dispatcher
            ->addMiddleware([
                $this->noopMiddleware, // 1st arg: object
                \BitFrame\Message\DiactorosResponseEmitter::class // 2nd arg: string
            ]);
        
        $response = $dispatcher->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
    
    public function testHandlePrependMiddleware()
    {
        $this->dispatcher
            ->attach('before.middleware', function ($event) {
                $this->assertInstanceOf(Event::class, $event);

                $data = $event->getParams();

                $this->assertInstanceOf(ResponseInterface::class, $data['response']);
            })
            ->attach('after.middleware', function ($event) {
                $this->assertInstanceOf(Event::class, $event);

                $data = $event->getParams();

                $this->assertInstanceOf(ResponseInterface::class, $data['response']);
            })
            ->attach('done.middleware', function ($event) {
                $this->assertInstanceOf(Event::class, $event);

                $data = $event->getParams();

                $this->assertInstanceOf(ResponseInterface::class, $data['response']);
            })
            // following should be executed second
            ->addMiddleware(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                $response->getBody()->write('Second');

                return $next($request, $response);
            })
            // following should be executed first
            ->prependMiddleware(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                $response->getBody()->write('First');

                return $next($request, $response);
            });
        
        $this->expectOutputString('FirstSecond');
        
        $response = $this->dispatcher->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        
        $output = $this->readStream($response->getBody());
        echo $output;
    }
    
    public function testHandleWithoutMiddleware() 
    {
        $this->dispatcher->addMiddleware([]);
        
        $this->expectException(\InvalidArgumentException::class);
        
        $this->dispatcher->handle($this->request);
    }
    
    public function testMiddlewareStateAtSpecificStagesOfExecution()
    {
        $dispatcher = $this->dispatcher;
        
        $dispatcher
            ->addMiddleware([
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($dispatcher) {
                    $this->assertTrue($dispatcher->isRunning());
                        
                    // more middlewares pending?
                    $this->assertTrue($dispatcher->hasMiddleware());
                    
                    return $next($request, $response);
                },
                // use the 'run' method instead of the PSR-15 'process'
                [$this->noopMiddleware, 'run'],
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($dispatcher) {
                    $this->assertTrue($dispatcher->isRunning());
                    
                    // all middlewares should have been exhausted at this point, therefore:
                    $this->assertFalse($dispatcher->hasMiddleware());

                    return $next($request, $response);
                }
            ]);
        
        $this->assertFalse($dispatcher->isRunning());
        
        // getPendingMiddleware() does not see nested middleware till parent middleware is executed
        $middlewares = $dispatcher->getPendingMiddleware();
        $this->assertTrue(count($middlewares) === 3);
        
        $response = $dispatcher->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        
        // at this point all middlewares have been run and none are left in queue, therefore:
        $middlewares = $dispatcher->getPendingMiddleware();
        $this->assertTrue(count($middlewares) === 0);
    }
    
    public function testHttpResponseBadRequestException()
    {
        $this->expectException(\BitFrame\Exception\BadRequestException::class);
        
        $this->invokeMethod($this->dispatcher, 'validateHttpResponse', [$this->request, HttpMessageFactory::createResponse(400)]);
    }
    
    public function testHttpResponseUnauthorizedException()
    {
        $this->expectException(\BitFrame\Exception\UnauthorizedException::class);
        
        $this->invokeMethod($this->dispatcher, 'validateHttpResponse', [$this->request, HttpMessageFactory::createResponse(401)]);
    }
    
    public function testHttpResponseForbiddenException()
    {
        $this->expectException(\BitFrame\Exception\ForbiddenException::class);
        
        $this->invokeMethod($this->dispatcher, 'validateHttpResponse', [$this->request, HttpMessageFactory::createResponse(403)]);
    }
    
    public function testHttpResponseRouteNotFoundException()
    {
        $this->expectException(\BitFrame\Exception\RouteNotFoundException::class);
        
        $this->invokeMethod($this->dispatcher, 'validateHttpResponse', [$this->request, HttpMessageFactory::createResponse(404)]);
    }
    
    public function testHttpResponseMethodNotAllowedException()
    {
        $this->expectException(\BitFrame\Exception\MethodNotAllowedException::class);
        
        $this->invokeMethod($this->dispatcher, 'validateHttpResponse', [$this->request, HttpMessageFactory::createResponse(405)]);
    }
    
    public function testHttpResponseInternalErrorException()
    {
        $this->expectException(\BitFrame\Exception\InternalErrorException::class);
        
        $this->invokeMethod($this->dispatcher, 'validateHttpResponse', [$this->request, HttpMessageFactory::createResponse(500)]);
    }
    
    public function testHttpResponseNotImplementedException()
    {
        $this->expectException(\BitFrame\Exception\NotImplementedException::class);
        
        $this->invokeMethod($this->dispatcher, 'validateHttpResponse', [$this->request, HttpMessageFactory::createResponse(501)]);
    }
    
    public function testHttpResponseServiceUnavailableException()
    {
        $this->expectException(\BitFrame\Exception\ServiceUnavailableException::class);
        
        $this->invokeMethod($this->dispatcher, 'validateHttpResponse', [$this->request, HttpMessageFactory::createResponse(503)]);
    }
    
    private function readStream($stream)
    {
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        
        $output = '';

        // no readable data in stream?
        if (! $stream->isReadable()) {
            $output = $stream;
        } else {
            // read data till end of stream is reached...
            while (! $stream->eof()) {
                // read 8mb (max buffer length) of binary data at a time and output it
                $output .= $stream->read(1024 * 8);
            }
        }
        
        return $output;
    }
    
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $params Array of parameters to pass into method.
     *
     * @return mixed
     */
    public function invokeMethod(&$object, $methodName, array $params = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(TRUE);

        return $method->invokeArgs($object, $params);
    }
}