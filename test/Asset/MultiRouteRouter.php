<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Asset;

use BitFrame\Router\AbstractRouter;

class MultiRouteRouter extends AbstractRouter
{
    private array $route = [];

    /**
     * {@inheritDoc}
     */
    public function map($methods, string $path, $handler)
    {
        if (is_string($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as $method) {
            $this->route[$method][$path] = [
                'method' => $method,
                'path' => $path,
                'handler' => $handler,
            ];
        }
    }

    public function getRouteData(string $method, string $path): array
    {
        return $this->route[$method][$path] ?? [];
    }
}
