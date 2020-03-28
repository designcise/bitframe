<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Unit;

use PHPUnit\Framework\TestCase;
use BitFrame\Factory\{HttpFactoryInterface, HttpFactory};
use BitFrame\Test\Asset\InteropMiddleware;
use InvalidArgumentException;

/**
 * @covers \BitFrame\Factory\HttpFactory
 */
class HttpFactoryTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testCanAddValidFactory()
    {
        $factory = $this->getMockBuilder(HttpFactoryInterface::class)
            ->getMock();

        HttpFactory::addFactory($factory);

        $this->assertSame($factory, HttpFactory::getFactory());
    }

    public function invalidFactoryProvider()
    {
        return [
            'random_string' => ['randomString'],
            'invalid_factory_object' => [new InteropMiddleware],
            'invalid_factory_class' => [InteropMiddleware::class],
        ];
    }

    /**
     * @dataProvider invalidFactoryProvider
     * 
     * @param object|string $factory
     */
    public function testShouldNotAddInvalidFactory($factory)
    {
        $this->expectException(InvalidArgumentException::class);

        HttpFactory::addFactory($factory);
    }
}