<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Http;

use BitFrame\Test\Asset\Controller;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use BitFrame\Test\Asset\AttributeRouteRouter;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \BitFrame\Router\AttributeRouteTrait
 */
class AttributeRouteTraitTest extends TestCase
{
    private AttributeRouteRouter $router;

    public function setUp(): void
    {
        $this->router = new AttributeRouteRouter();
    }

    public function attributeRouteProvider(): array
    {
        return [
            'Attribute Route' => [
                ['GET', '/test'],
                'bar',
            ],
            'Same Attribute Route with another method' => [
                ['POST', '/test'],
                'bar',
            ],
            'Multiple Route attributes on same method' => [
                ['POST', '/test-2'],
                'bar',
            ],
            'Route declared on a static method' => [
                ['PUT', '/static-method'],
                'bar',
            ],
        ];
    }

    /**
     * @dataProvider attributeRouteProvider
     *
     * @throws \ReflectionException
     */
    public function testAddRoutesWithAttribute(array $routeData, string $expectedOutput)
    {
        $this->router->registerControllers([
            new Controller(),
        ]);

        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMockForAbstractClass();

        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->onlyMethods(['handle'])
            ->getMockForAbstractClass();

        $routeData = $this->router->getRouteData(...$routeData);

        /** @var ResponseInterface $response */
        $response = $routeData['handler']($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->expectOutputString($expectedOutput);
    }
}
