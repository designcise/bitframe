<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Unit;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface
};
use BitFrame\Factory\{HttpFactory};
use BitFrame\Test\Asset\{HttpFactoryInterface, InteropMiddleware};
use InvalidArgumentException;
use RuntimeException;

use function get_class;

/**
 * @covers \BitFrame\Factory\HttpFactory
 */
class HttpFactoryTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testCanAddValidFactory(): void
    {
        $factory = $this->getMockBuilder(HttpFactoryInterface::class)
            ->getMock();

        HttpFactory::addFactory($factory);

        $this->assertSame($factory, HttpFactory::getFactory());
    }

    public function invalidFactoryProvider(): array
    {
        return [
            'random_string' => ['randomString'],
            'invalid_factory_object' => [new InteropMiddleware],
            'invalid_factory_class' => [InteropMiddleware::class],
            'implements some PSR-17 Factories' => [
                $this->getMockBuilder([
                    RequestFactoryInterface::class,
                    ResponseFactoryInterface::class,
                    ServerRequestFactoryInterface::class,
                ])->getMock()
            ],
        ];
    }

    /**
     * @dataProvider invalidFactoryProvider
     * 
     * @param object|string $factory
     */
    public function testShouldNotAddInvalidFactory($factory): void
    {
        $this->expectException(InvalidArgumentException::class);

        HttpFactory::addFactory($factory);
    }

    /**
     * @runInSeparateProcess
     *
     * @throws \ReflectionException
     */
    public function testNoFactoriesFound(): void
    {
        $reflection = new ReflectionClass(HttpFactory::class);
        $property = $reflection->getProperty('factoriesList');
        $property->setAccessible(true);
        $property->setValue([]);

        $this->expectException(RuntimeException::class);

        HttpFactory::getFactory();
    }

    /**
     * @runInSeparateProcess
     */
    public function testCanResolveCustomFactoryByClassName(): void
    {
        $customFactory = $this->getMockBuilder(HttpFactoryInterface::class)
            ->getMock();

        HttpFactory::addFactory($customFactory);

        $this->assertInstanceOf(get_class($customFactory), HttpFactory::getFactory());
    }

    /**
     * @runInSeparateProcess
     *
     * @throws \ReflectionException
     */
    public function testSkipsAndRemovesNonExistingFactory(): void
    {
        $reflection = new ReflectionClass(HttpFactory::class);
        $property = $reflection->getProperty('factoriesList');
        $property->setAccessible(true);
        $property->setValue([
            '\Non\Existent\Factory',
            ...$property->getValue('factoriesList'),
        ]);

        HttpFactory::getFactory();

        $propertyAfter = $reflection->getProperty('factoriesList');
        $propertyAfter->setAccessible(true);
        $activeFactoriesList = $propertyAfter->getValue('factoriesList');

        $this->assertNotContains('\Non\Existent\Factory', $activeFactoriesList);
    }
}
