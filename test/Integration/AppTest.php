<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Integration;

use BitFrame\Factory\HttpFactory;
use PHPUnit\Framework\TestCase;
use BitFrame\App;
use BitFrame\Test\Asset\HelloWorldMiddleware;

/**
 * @covers \BitFrame\App
 */
class AppTest extends TestCase
{
    private App $app;

    public function setUp(): void
    {
        $this->app = new App();
    }

    public function testHandleMethodResponseShouldHaveEmptyBody(): void
    {
        $this->app->use(HelloWorldMiddleware::class);

        $request = HttpFactory::createServerRequest('HEAD', '/', []);
        $response = $this->app->handle($request);

        $this->assertEmpty((string)$response->getBody());
    }

    public function testCanWriteToStream(): void
    {
        $this->app->write('Hello world!');

        $this->assertSame('Hello world!', (string)$this->app->getResponse()->getBody());
    }

    public function testIsXhrRequest(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/', [])
            ->withHeader('X-Requested-With', 'XMLHttpRequest');
        $app = new App(null, $request);

        $this->assertTrue($app->isXhrRequest());
    }

    public function testGetRequest(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/', []);
        $app = new App(null, $request);

        $this->assertSame($request, $app->getRequest());
    }

    public function testGetResponse(): void
    {
        $request = HttpFactory::createServerRequest('GET', '/', []);
        $app = new App(null, $request);

        $this->assertSame($request, $app->getRequest());
    }
}