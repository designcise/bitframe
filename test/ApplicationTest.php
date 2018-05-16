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

use \Interop\Http\Factory\ResponseFactoryInterface;
use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use \Psr\Http\Server\RequestHandlerInterface;

use \BitFrame\Application;
use \BitFrame\Data\ApplicationData;
use \BitFrame\EventManager\Event;
use \BitFrame\Dispatcher\DispatcherInterface;
use \BitFrame\Router\{RouteCollectionInterface, RouterInterface};
use \BitFrame\Factory\HttpMessageFactory;
use \BitFrame\Test\Asset\InteropMiddleware;

/**
 * @covers \BitFrame\Application
 */
class ApplicationTest extends TestCase
{
    /** @var \BitFrame\Application */
    private $app;
    
    /** @var \BitFrame\Message\ResponseEmitterInterface */
    private $responder;
    
    protected function setUp()
    {
        $this->app = new Application();
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
    
    public function testApplicationWithDefaultConfiguration()
    {
        $app = new Application([
            // default dispatcher
            DispatcherInterface::class => null,
            
            // default request prototype
            ServerRequestInterface::class => null,
            // default response prototype
            ResponseInterface::class => null,
            
            // default route collection
            RouteCollectionInterface::class => null
        ]);
        
        $this->assertInstanceOf(DispatcherInterface::class, $app->getDispatcher());
        $this->assertInstanceOf(ServerRequestInterface::class, $app->getRequest());
        $this->assertInstanceOf(ResponseInterface::class, $app->getResponse());
        $this->assertInstanceOf(RouteCollectionInterface::class, $app->getRouteCollection());
        
        $this->assertInstanceOf(ApplicationData::class, $app->getData());
    }
    
    public function testApplicationReset()
    {
        $app = new Application([
            // dummy custom dispatcher
            DispatcherInterface::class => new class extends InteropMiddleware implements DispatcherInterface {
                public function addMiddleware($middleware) {
                    echo 'custom dispatcher';
                    
                    return $this;
                }
    
                public function clear() {}

                public function getPendingMiddleware(): array {
                    return [];
                }

                public function getProcessedMiddleware(): array {
                    return [];
                }

                public function isRunning(): bool {
                    return true;
                }

                public function hasMiddleware(): bool {
                    return true;
                }
                
                public function handle(ServerRequestInterface $request): ResponseInterface {
                    return HttpMessageFactory::createResponse();
                }
            }
        ]);
        
        $this->assertInstanceOf(InteropMiddleware::class, $app->getDispatcher());
        
        $app['test'] = 'val';
        
        $data = $app->getDataArray();
        
        $this->assertArrayHasKey('test', $data);
        $this->assertEquals($data['test'], 'val');
        
        $this->assertInstanceOf(DispatcherInterface::class, $app->getDispatcher());
        
        $app->addMiddleware([]);
        
        // reset
        $app->reset();
        
        $this->assertInstanceOf(InteropMiddleware::class, $app->getDispatcher());
        
        // re-validate values
        $data = $app->getDataArray();
        $this->assertArrayNotHasKey('test', $data);
        
        $this->assertInstanceOf(DispatcherInterface::class, $app->getDispatcher());
        $this->expectOutputString('custom dispatchercustom dispatcher');
        
        $app->addMiddleware([]);
    }
    
    public function testApplicationDataWithApplicationDataAsKey()
    {
        $app = $this->app;
        
        $this->expectException(\InvalidArgumentException::class);
        
        $app[ApplicationData::class] = 'test';
    }
    
    public function testApplicationDataWithRouteCollectionInterfaceAsKey()
    {
        $app = $this->app;
        
        $this->expectException(\InvalidArgumentException::class);
        
        $app[RouteCollectionInterface::class] = 'test';
    }
    
    public function testApplicationDataContainsCorrectValues()
    {
        $app = $this->app;
        $app['test1'] = 'val1';
        $app['test2'] = 'val2';
        
        $data = $app->getDataArray();
        
        $this->assertArrayHasKey('test1', $data);
        $this->assertArrayHasKey('test2', $data);
        $this->assertEquals($data['test1'], 'val1');
        $this->assertEquals($data['test2'], 'val2');
    }
    
    public function testApplicationCanShareDataAmongAllMiddlewares()
    {
        $app = $this->app;
        
        $app
            ->addMiddleware([
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                    $data = $request->getAttribute(ApplicationData::class);
                    $data['a'] = '1';
                    
                    $app->run([
                        function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                            $data = $request->getAttribute(ApplicationData::class);
                            $data['a1'] = '1.1';

                            return $next($request, $response);
                        },
                        function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                            $data = $request->getAttribute(ApplicationData::class);
                            $data['a2'] = '1.2';

                            return $next($request, $response);
                        }
                    ]);

                    return $next($request, $response);
                },
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                    $data = $request->getAttribute(ApplicationData::class);
                    $data['a'] = '0';
                    $data['b'] = '2';

                    return $next($request, $response);
                },
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                    $data = $request->getAttribute(ApplicationData::class);
                    $data['c'] = '3';

                    return $next($request, $response);
                },
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                    $data = $request->getAttribute(ApplicationData::class);

                    $this->assertSame([
                        'a' => '0',
                        'a1' => '1.1',
                        'a2' => '1.2',
                        'b' => '2',
                        'c' => '3'
                    ], $data->getData());
                    
                    return $next($request, $response);
                }
            ])
            ->run();
        
        $app->run([
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                $data = $request->getAttribute(ApplicationData::class);
                $data['d'] = '4';

                return $next($request, $response);
            },
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                $data = $request->getAttribute(ApplicationData::class);
                $data['d'] = '2';
                $data['e'] = '5';

                $this->assertSame([
                    'a' => '0',
                    'a1' => '1.1',
                    'a2' => '1.2',
                    'b' => '2',
                    'c' => '3',
                    'd' => '2',
                    'e' => '5'
                ], $data->getData());

                return $next($request, $response);
            }
        ]);
        
        $app->
            addMiddleware(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                $data = $request->getAttribute(ApplicationData::class);
                $data['d'] = '1';

                $this->assertSame([
                    'a' => '0',
                    'a1' => '1.1',
                    'a2' => '1.2',
                    'b' => '2',
                    'c' => '3',
                    'd' => '1',
                    'e' => '5'
                ], $data->getData());

                return $next($request, $response);
            })
            ->run();
    }
    
    public function testOriginalRequest()
    {
        $app = $this->app;
        
        // initialize app with $request1
        $request1 = HttpMessageFactory::createServerRequest('GET', 'https://www.one.com');
        $app = new Application([ServerRequestInterface::class => $request1]);
        
        // change app request to $request2
        $request2 = HttpMessageFactory::createServerRequest('GET', 'https://www.two.com');
        $app->setRequest($request2);
        
        // retrieve current + original requests
        $currAppRequest = $app->getRequest();
        $initAppRequest = $app->getOriginalRequest();
        
        // compare requests
        $this->assertSame($request1->getUri()->getHost(), $initAppRequest->getUri()->getHost());
        $this->assertSame($request2->getUri()->getHost(), $currAppRequest->getUri()->getHost());
    }
    
    public function testEndpointsAndQueryString()
    {
        $request = HttpMessageFactory::createServerRequest('GET', 'https://user:pass@www.example.com/doc/v1/install/?key1=value1&key2=value2');
        
        $app = new Application([ServerRequestInterface::class => $request]);
        $endpoints = $app->getRequest()->getEndpoints();
        
        $appReq = $app->getRequest();
        
        // test if url was parsed properly
        $this->assertSame('user:pass', $appReq->getUri()->getUserInfo());
        
        // single
        $this->assertTrue($appReq->hasEndpoint('doc/'));
        $this->assertTrue($appReq->hasEndpoint('v1/'));
        $this->assertTrue($appReq->hasEndpoint('install/'));
        
        // double
        $this->assertTrue($appReq->hasEndpoint('doc/v1/'));
        $this->assertTrue($appReq->hasEndpoint('v1/', 'doc/'));
        $this->assertTrue($appReq->hasEndpoint('v1/install'));
        $this->assertTrue($appReq->hasEndpoint('install', 'v1/'));
        $this->assertTrue($appReq->hasEndpoint(['install', 'quickstart'], 'v1/'));
        
        $this->assertFalse($appReq->hasEndpoint('doc/install'));
        
        // tripple
        $this->assertTrue($appReq->hasEndpoint('install', 'doc/v1/'));
        $this->assertTrue($appReq->hasEndpoint('v1/install', 'doc/'));
        $this->assertTrue($appReq->hasEndpoint('doc/v1/install', ''));
        $this->assertTrue($appReq->hasEndpoint('doc/v1/install'));
        
        $this->assertTrue($appReq->hasEndpoint(['install', 'quickstart'], 'doc/v1/'));
        $this->assertTrue($appReq->hasEndpoint(['v1/install', 'quickstart'], 'doc/'));
        
        $this->assertTrue($appReq->hasEndpoint('doc/v1/install', '', true));
        $this->assertTrue($appReq->hasEndpoint('', 'doc/v1/install', true));
        $this->assertTrue($appReq->hasEndpoint('v1/install', 'doc/', true));
        
        $this->assertFalse($appReq->hasEndpoint('doc/v1/', '', true)); // strict (same as isEndpoint())
        
        $this->assertTrue($appReq->isEndpoint('doc/v1/install/'));
        $this->assertTrue($appReq->isEndpoint(['install/', 'quickstart'], 'doc/v1/'));
        $this->assertTrue($appReq->isEndpoint(['v1/install/', 'v1/quickstart'], 'doc/'));
        
        $this->assertFalse($appReq->isEndpoint('doc/v1/'));
        $this->assertFalse($appReq->isEndpoint('v1/install'));
        $this->assertFalse($appReq->isEndpoint(['install', 'quickstart'], 'v1/'));
        $this->assertFalse($appReq->isEndpoint('v1', 'doc/'));
        
        $this->assertSame('doc', $endpoints[0]);
        $this->assertSame('v1', $endpoints[1]);
        $this->assertSame('install', $endpoints[2]);
        
        $this->assertSame('doc', $appReq->getEndpoint(1));
        $this->assertSame('v1', $appReq->getEndpoint(2));
        $this->assertSame('install', $appReq->getEndpoint(3));
        
        $querystr = $appReq->getQueryParams();
        
        $this->assertSame('value1', $querystr['key1']);
        $this->assertSame('value2', $querystr['key2']);
        
        $this->assertSame('value1', $appReq->getQueryParam('key1'));
        $this->assertSame('value2', $appReq->getQueryParam('key2'));
    }
    
    public function testResponseWithJson()
    {
        $app = $this->app;
        
        $response = HttpMessageFactory::createResponse(200);
        $response->getBody()->write('hello world!');
        
        $router = $this->createMock(\BitFrame\Router\RouterInterface::class);
        $router->method('process')->willReturn($response->withJson(['test' => '123'], null, 'body'));
        
        $app
            ->addMiddleware([
                $this->responder,
                $router
            ])
            ->run();
        
        $data = json_decode($app->getResponse()->getBody(), true);
        $expected = ['test' => '123', 'body' => 'hello world!'];
        
        $this->assertSame($app->getResponse()->getHeader('Content-Type')[0], 'application/json;charset=utf-8');
        $this->assertSame(array_diff($expected, $data), array_diff($data, $expected));
    }
    
    public function testResponseWithRedirect()
    {
        $app = $this->app;
        
        $response = HttpMessageFactory::createResponse(200);
        
        $router = $this->createMock(\BitFrame\Router\RouterInterface::class);
        $router->method('process')->willReturn($response->withRedirect('/2', 302));
        
        $app
            ->addMiddleware([
                $this->responder,
                $router
            ])
            ->run();
        
        $response = $app->getResponse();
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame(['/2'], $response->getHeader('Location'));
        $this->assertSame(302, $response->getStatusCode());
    }
    
    public function testResponseWithInvalidRedirectStatusCode()
    {
        $app = $this->app;
        
        $response = HttpMessageFactory::createResponse(200);
        
        $this->expectException(\BitFrame\Exception\HttpException::class);
        
        $router = $this->createMock(\BitFrame\Router\RouterInterface::class);
        $router->method('process')->willReturn($response->withRedirect('/2', 404));
        
        $app
            ->addMiddleware([
                $this->responder,
                $router
            ])
            ->run();
    }
    
    public function testRunMiddlewareAndEvents()
    {
        $dispatcher = new \BitFrame\Dispatcher\MiddlewareDispatcher();
        
        $eventManager = $this->getMockBuilder(\BitFrame\EventManager\EventManager::class)->setMethods(['getListeners'])->getMock();
        
        $dispatcher->setEventManager($eventManager);
        
        $eventManager->expects($this->exactly(6))->method('getListeners');
        
        $app = new Application([
            DispatcherInterface::class => $dispatcher
        ]);
        
        $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('Run Middleware');
            return $response;
        });
        
        $this->expectOutputString('Run Middleware');
        
        $app
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
            ->addMiddleware([
                $this->responder,
                $this->getDummyRouter()
            ])
            ->run();
    }
    
    public function testExecMiddleware()
    {
        $app = $this->app;
        
        $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('Execute Middleware');
            return $response;
        });
        
        $this->expectOutputString('Execute Middleware');
        
        $app->run([
            $this->responder,
            $this->getDummyRouter()
        ]);
    }
    
    public function testNestedExecMiddleware()
    {
        $app = $this->app;
        
        $this->expectOutputString('<h1>Execute Parent Middleware</h1><h1>Execute Child Middleware #1</h1><h1>Execute Child Middleware #2</h1>');
        
        $app->run([
            $this->responder,
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                $response->getBody()->write('<h1>Execute Parent Middleware</h1>');

                $app->run([
                    function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                        $response->getBody()->write('<h1>Execute Child Middleware #1</h1>');

                        return $next($request, $response);
                    },
                    function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                        $response->getBody()->write('<h1>Execute Child Middleware #2</h1>');

                        return $next($request, $response);
                    }
                ]);

                return $next($request, $response);
            }
        ]);
    }
    
    public function testDeepLevelNestedExecMiddleware()
    {
        $dispatcher = new \BitFrame\Dispatcher\MiddlewareDispatcher();
        
        $eventManager = $this->getMockBuilder(\BitFrame\EventManager\EventManager::class)->setMethods(['getListeners'])->getMock();
        
        $dispatcher->setEventManager($eventManager);
        
        // 6 middleware x 3 events = 18 times 'getListeners' would be called
        $eventManager
            ->expects($this->exactly(18))
            ->method('getListeners');
        
        $app = new Application([
            DispatcherInterface::class => $dispatcher
        ]);
        
        $this->expectOutputString('<h1>Execute Parent Middleware</h1><h1>Execute Child Middleware #1</h1><h1>Execute Child #1 Level #1 Deep Middleware</h1><h1>Execute Child #1 Level #2 Deep Middleware</h1><h1>Execute Child Middleware #2</h1>');
        
        $app->run([
            $this->responder,
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                $response->getBody()->write('<h1>Execute Parent Middleware</h1>');

                $app->run([
                    // child #1
                    function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                        $response->getBody()->write('<h1>Execute Child Middleware #1</h1>');

                        // child #1; 1 level deep
                        $app->run(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                            $response->getBody()->write('<h1>Execute Child #1 Level #1 Deep Middleware</h1>');

                            // child #1; 2 level deep 
                            $app->run(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                                $response->getBody()->write('<h1>Execute Child #1 Level #2 Deep Middleware</h1>');

                                return $next($request, $response);
                            });
                            
                            return $next($request, $response);
                        });
                        
                        return $next($request, $response);
                    },
                    // child #2
                    function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                        $response->getBody()->write('<h1>Execute Child Middleware #2</h1>');

                        return $next($request, $response);
                    }
                ]);

                return $next($request, $response);
            }
        ]);
    }
    
    public function testNestedRunMiddleware()
    {
        $app = $this->app;
        
        $this->expectOutputString('<h1>Run Parent Middleware</h1><h1>Run Child Middleware #1</h1><h1>Run Child Middleware #2</h1>');
        
        $app
            ->addMiddleware([
                $this->responder,
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                    $response->getBody()->write('<h1>Run Parent Middleware</h1>');
                    
                    $app->addMiddleware([
                        function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                            $response->getBody()->write('<h1>Run Child Middleware #1</h1>');

                            return $next($request, $response);
                        },
                        function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                            $response->getBody()->write('<h1>Run Child Middleware #2</h1>');

                            return $next($request, $response);
                        }
                    ]);

                    return $next($request, $response);
                }
            ])
            ->run();
    }
    
    public function testDeepLevelNestedRunMiddleware() 
    {
        $dispatcher = new \BitFrame\Dispatcher\MiddlewareDispatcher();
        
        $eventManager = $this->getMockBuilder(\BitFrame\EventManager\EventManager::class)->setMethods(['getListeners'])->getMock();
        
        $dispatcher->setEventManager($eventManager);
        
        // 5 middleware x 3 events = 15 times 'getListeners' would be called
        $eventManager
            ->expects($this->exactly(15))
            ->method('getListeners');
        
        $app = new Application([
            DispatcherInterface::class => $dispatcher
        ]);
        
        $this->expectOutputString('<h1>Run Parent Middleware</h1><h1>Run Child Middleware #1</h1><h1>Run Child Middleware #2</h1><h1>Run Deep Child Middleware</h1>');
        
        $app
            ->addMiddleware([
                $this->responder,
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                    $response->getBody()->write('<h1>Run Parent Middleware</h1>');
                    
                    $app->addMiddleware([
                        function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                            $response->getBody()->write('<h1>Run Child Middleware #1</h1>');

                            // it's added to the end of the call stack, so this would run at the very end
                            $app->addMiddleware(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                                $response->getBody()->write('<h1>Run Deep Child Middleware</h1>');

                                return $next($request, $response);
                            });
                            
                            return $next($request, $response);
                        },
                        function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                            $response->getBody()->write('<h1>Run Child Middleware #2</h1>');

                            return $next($request, $response);
                        }
                    ]);

                    return $next($request, $response);
                }
            ])
            ->run();
    }
    
    public function testExecAndNestedRunMiddlewareRequestRelay()
    {
        $app = $this->app;
        
        $this->expectOutputString('<h1>Mix Exec Parent</h1><h1>Mix Run Child #1</h1><h1>Mix Run Child #2</h1>');
        
        $app
            ->run([
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                    $response->getBody()->write('<h1>Mix Exec Parent</h1>');
                    
                    $app->
                        addMiddleware([
                            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                                $response->getBody()->write('<h1>Mix Run Child #1</h1>');

                                return $next($request, $response);
                            },
                            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                                $response->getBody()->write('<h1>Mix Run Child #2</h1>');

                                return $next($request, $response);
                            }
                        ]);

                    return $next($request, $response);
                }
            ])
            ->addMiddleware($this->responder)
            ->run();
    }
    
    public function testRunAndNestedExecMiddlewareRequestRelay()
    {
        $app = $this->app;
        
        $this->expectOutputString('<h1>Mix Run Parent</h1><h1>Mix Exec Child #1</h1><h1>Mix Exec Child #2</h1>');
        
        $app
            ->addMiddleware([
                $this->responder,
                function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use (&$app) {
                    $response->getBody()->write('<h1>Mix Run Parent</h1>');
                    
                    $app->run([
                        function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                            $response->getBody()->write('<h1>Mix Exec Child #1</h1>');

                            return $next($request, $response);
                        },
                        function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                            $response->getBody()->write('<h1>Mix Exec Child #2</h1>');

                            return $next($request, $response);
                        }
                    ]);

                    return $next($request, $response);
                }
            ])
            ->run();
    }
    
    public function testRunWithoutMiddleware()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->app->run();
    }
    
    public function testExecWithoutMiddleware()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->app->run([]);
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
            
            public function createResponse($code = 200): ResponseInterface
            {
                if ($this->guzzleExists) {
                    return new \GuzzleHttp\Psr7\Response($code);
                }

                throw new \RuntimeException('Unable to create a response. Default PSR-7 stream libraries not found.');
            }
        };
        
        // set custom
        HttpMessageFactory::setResponseFactory($customResponseFactory);

        $app = new Application();
        
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
                if (! empty($appRoutes = $request->getAttribute(\BitFrame\Router\RouteCollectionInterface::class, []))) {
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