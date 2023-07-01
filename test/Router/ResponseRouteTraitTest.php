<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Router;

use BitFrame\Test\Asset\ResponseTypeRouter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

use function mime_content_type;

/**
 * @covers \BitFrame\Router\ResponseRouteTrait
 */
class ResponseRouteTraitTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../Asset/';

    private ResponseTypeRouter $router;

    public function setUp(): void
    {
        $this->router = new ResponseTypeRouter();
    }

    public function testText(): void
    {
        $this->router->text(['GET', 'POST'], '/hello/world', 'Testing 123', 202);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Testing 123', (string) $response->getBody());
        $this->assertSame('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));

        $routeData = $this->router->getRouteDataByMethod('POST');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Testing 123', (string) $response->getBody());
        $this->assertSame('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
    }

    public function testHtml(): void
    {
        $this->router->html(['GET', 'POST'], '/hello/world', '<h1>Testing 123</h1>', 202);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('<h1>Testing 123</h1>', (string) $response->getBody());
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('content-type'));

        $routeData = $this->router->getRouteDataByMethod('POST');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('<h1>Testing 123</h1>', (string) $response->getBody());
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('content-type'));
    }

    public function testJson(): void
    {
        $this->router->json(['GET', 'POST'], '/hello/world', [
            'name' => 'John',
            'age' => 30,
            'car' => null,
        ], 202);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('{"name":"John","age":30,"car":null}', (string) $response->getBody());
        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));

        $routeData = $this->router->getRouteDataByMethod('POST');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('{"name":"John","age":30,"car":null}', (string) $response->getBody());
        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testJsonp(): void
    {
        $this->router->jsonp(['GET', 'POST'], '/hello/world', [
            'name' => 'John',
            'age' => 30,
            'car' => null,
        ], 'myCallback', 202);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('myCallback({"name":"John","age":30,"car":null})', (string) $response->getBody());
        $this->assertSame('application/javascript; charset=utf-8', $response->getHeaderLine('Content-Type'));

        $routeData = $this->router->getRouteDataByMethod('POST');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('myCallback({"name":"John","age":30,"car":null})', (string) $response->getBody());
        $this->assertSame('application/javascript; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testXml(): void
    {
        $this->router->xml(['GET', 'POST'], '/hello/world', '<test>Test XML</test>', 202);

        $routeData = $this->router->getRouteDataByMethod('GET');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('GET', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame(202, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('<test>Test XML</test>', (string) $response->getBody());
        $this->assertSame('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));

        $routeData = $this->router->getRouteDataByMethod('POST');
        /** @var ResponseInterface $response */
        $response = $routeData['handler']();

        $this->assertEquals('POST', $routeData['method']);
        $this->assertSame('/hello/world', $routeData['path']);
        $this->assertSame(202, $response->getStatusCode());
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
}
