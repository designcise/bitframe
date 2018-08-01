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

use \Psr\Http\Message\{ServerRequestFactoryInterface, ResponseFactoryInterface, StreamFactoryInterface, UriFactoryInterface};
use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use \Psr\Http\Server\RequestHandlerInterface;

use \BitFrame\Application;
use \BitFrame\Factory\HttpMessageFactory;
use \BitFrame\Router\{RouteCollectionInterface, RouterInterface};

/**
 * @covers \BitFrame\Factory\HttpMessageFactory
 */
class HttpMessageFactoryTest extends TestCase
{
    /** @var \BitFrame\Message\ResponseEmitterInterface */
    private $responder;
    
    protected function setUp()
    {
        $this->responder = new class implements \Psr\Http\Server\MiddlewareInterface {
            // PSR-15 middleware
            public function process(
                ServerRequestInterface $request, 
                RequestHandlerInterface $handler
            ): ResponseInterface 
            {
                // continue processing all requests
                $response = $handler->handle($request);

                $body = $response->getBody();

                if ($body->isSeekable()) {
                    $body->rewind();
                }

                // no readable data in stream?
                if (! $body->isReadable()) {
                    echo $body;
                } else {
                    // read data till end of stream is reached...
                    while (! $body->eof()) {
                        // read 8mb (max buffer length) of binary data at a time and output it
                        echo $body->read(1024 * 8);
                    }
                }

                return $response;
            }
        };
    }
    
    public function testCustomResponseFactory()
    {
        $guzzleExists = class_exists('GuzzleHttp\\Psr7\\Response');
        
        $customResponseFactory = new class($guzzleExists) implements ResponseFactoryInterface {
            private $guzzleExists;
            
            public function __construct(bool $guzzleExists) 
            {
                $this->guzzleExists = $guzzleExists;
            }
            
            public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
            {
                if ($this->guzzleExists) {
                    return new \GuzzleHttp\Psr7\Response($code, [], null, '1.1', $responsePhrase);
                }

                throw new \RuntimeException('Unable to create a response. Default PSR-7 stream libraries not found.');
            }
        };
        
        // set custom
        HttpMessageFactory::setResponseFactory($customResponseFactory);

        $app = new \BitFrame\Application();
        
        if (! $guzzleExists) {
            $this->expectException(\RuntimeException::class);
        }
        
        $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($guzzleExists) {
            if ($guzzleExists) {
                $this->assertInstanceOf(\GuzzleHttp\Psr7\Response::class, $response);
            }
            
            return $response;
        });
        
        $app->run([
            $this->responder,
            $this->getDummyRouter()
        ]);
        
        // restore
        HttpMessageFactory::setResponseFactory(new \BitFrame\Factory\ResponseFactory());
    }
    
    private function getDummyRouter()
    {
        return new class implements RouterInterface {
            use \BitFrame\Router\RouterTrait;
            use \BitFrame\Dispatcher\DispatcherAwareTrait;
            
            // PSR-15 middleware
            public function process(
                ServerRequestInterface $request, 
                RequestHandlerInterface $handler
            ): ResponseInterface 
            {
                // fallback if no route was defined
                $callback = function($request, $response, $next) {
                    return $next($request, $response);
                };
                
                // get route data
                if (! empty($appRoutes = $request->getAttribute(RouteCollectionInterface::class, []))) {
                    $route = $appRoutes->getData();
                    
                    if (isset($route[0])) {
                        // this is a very basic implementation of a router so only the first 
                        // defined callback is executed
                        $callback = $route[0]->getCallable();
                    }
                }
                
                $dispatcher = $this->getDispatcher();
            
                // execute any/all router middleware queued up in 'dispatcher'
                $this->response = $dispatcher
                                    // 1: first share application-level response (so far) with router
                                    ->setResponse($handler->getResponse())
                                    // 2: add route as middleware (to the front of middleware queue)
                                    ->prependMiddleware($callback)
                                    // 3: then proceed to handling router-level middleware + route itself
                                    ->handle($request);

                // 4: update handler's response to match response generated from router + middleware
                $handler->setResponse($this->response);

                return $handler->handle($request);
            }
        };
    }
}