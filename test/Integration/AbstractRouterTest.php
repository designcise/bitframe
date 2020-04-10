<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Integration;

use Closure;
use PHPUnit\Framework\TestCase;
use BitFrame\Router\AbstractRouter;
use BitFrame\Test\Asset\SingleRouteRouter;
use Psr\Http\Message\ResponseInterface;

use function mime_content_type;

/**
 * @covers \BitFrame\Router\AbstractRouter
 */
class AbstractRouterTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';

    private AbstractRouter $router;

    public function setUp(): void
    {
        $this->router = new SingleRouteRouter();
    }

    public function testGet(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->get('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('GET');

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testPost(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->post('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('POST');

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testPut(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->put('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('PUT');

        $this->assertEquals('PUT', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testPatch(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->patch('/hello/world', $handler);
        $routeData = $this->router->getRouteDataByMethod('PATCH');

        $this->assertEquals('PATCH', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testDelete(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->delete('/hello/world', $handler);
        $routeData = $this->router->getRouteDataByMethod('DELETE');

        $this->assertEquals('DELETE', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testHead(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->head('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('HEAD');

        $this->assertEquals('HEAD', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testOptions(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->options('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('OPTIONS');

        $this->assertEquals('OPTIONS', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testAny(): void
    {
        $handler = $this->getRouteHandler();
        $this->router->any('/hello/world', $handler);

        $routeData = $this->router->getRouteDataByMethod('GET');
        $this->assertSame('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('POST');
        $this->assertSame('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PUT');
        $this->assertSame('PUT', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PATCH');
        $this->assertSame('PATCH', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('DELETE');
        $this->assertSame('DELETE', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('OPTIONS');
        $this->assertSame('OPTIONS', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testGroup(): void
    {
        $handler = $this->getRouteHandler();

        $this->router->group('/hello', static function (AbstractRouter $route) use ($handler) {
            $route->get('/world', $handler);
            $route->post('/world', $handler);
            $route->put('/world', $handler);
            $route->patch('/world', $handler);
            $route->delete('/world', $handler);
            $route->options('/world', $handler);
            $route->head('/world', $handler);
        });

        $routeData = $this->router->getRouteDataByMethod('GET');
        $this->assertSame('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('POST');
        $this->assertSame('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PUT');
        $this->assertSame('PUT', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('PATCH');
        $this->assertSame('PATCH', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('DELETE');
        $this->assertSame('DELETE', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);

        $routeData = $this->router->getRouteDataByMethod('OPTIONS');
        $this->assertSame('OPTIONS', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame($handler, $routeData['handler']);
    }

    public function testText(): void
    {
        $this->router->text(['GET', 'POST'], '/hello/world', 'Testing 123', 200);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Testing 123', (string) $response->getBody());
        $this->assertSame('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));

        $routeData = $this->router->getRouteDataByMethod('POST');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Testing 123', (string) $response->getBody());
        $this->assertSame('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
    }

    public function testHtml(): void
    {
        $this->router->html(['GET', 'POST'], '/hello/world', '<h1>Testing 123</h1>', 200);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('<h1>Testing 123</h1>', (string) $response->getBody());
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('content-type'));

        $routeData = $this->router->getRouteDataByMethod('POST');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('<h1>Testing 123</h1>', (string) $response->getBody());
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('content-type'));
    }

    public function testXml(): void
    {
        $this->router->xml(['GET', 'POST'], '/hello/world', '<test>Test XML</test>', 200);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('<test>Test XML</test>', (string) $response->getBody());
        $this->assertSame('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));

        $routeData = $this->router->getRouteDataByMethod('POST');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('<test>Test XML</test>', (string) $response->getBody());
        $this->assertSame('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testFile(): void
    {
        $file = self::ASSETS_DIR . 'test.txt';
        $this->router->file('/hello/world', $file);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertSame('test', (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(mime_content_type($file), $response->getHeaderLine('Content-Type'));
    }

    public function testDownload(): void
    {
        $file = self::ASSETS_DIR . 'test.txt';
        $this->router->download('/hello/world', $file, 'foo.txt');

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $dispositionHeader = 'attachment; filename=foo.txt; filename*=UTF-8\'\'' . rawurlencode('foo.txt');

        $this->assertSame($dispositionHeader, $response->getHeaderLine('content-disposition'));
        $this->assertSame('text/plain', $response->getHeaderLine('content-type'));
        $this->assertSame('test', (string) $response->getBody());
    }

    public function testRedirect(): void
    {
        $this->router->redirect('/hello/world', '/test', 307);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertSame(307, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('/test', $response->getHeaderLine('Location'));
    }

    private function getRouteHandler(): Closure
    {
        return fn ($req, $handler) => $handler->handle($req);
    }
}
