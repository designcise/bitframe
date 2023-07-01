<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Router;

use ReflectionClass;
use ReflectionMethod;

trait AttributeRouteTrait
{
    use RouterTrait;

    /**
     * @throws \ReflectionException
     */
    public function registerControllers(array $controllers): void
    {
        foreach ($controllers as $controller) {
            $this->registerControllerRoutes($controller);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function registerControllerRoutes($controller): void
    {
        $reflectionClass = new ReflectionClass($controller);
        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($reflectionMethods as $method) {
            $this->registerMethodRoutes($controller, $method);
        }
    }

    private function registerMethodRoutes($controller, $method): void
    {
        $attributes = $method->getAttributes(Route::class);

        foreach ($attributes as $attribute) {
            /** @var Route $route */
            $route = $attribute->newInstance();
            $this->map(
                $route->getMethods(),
                $route->getPath(),
                [$controller, $method->getName()],
            );
        }
    }
}
