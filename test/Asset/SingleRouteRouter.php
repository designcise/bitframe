<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Test\Asset;

use BitFrame\Router\AbstractRouter;

class SingleRouteRouter extends AbstractRouter
{
    private array $route = [];

    /**
     * {@inheritDoc}
     */
    public function map($methods, string $path, $handler)
    {
        foreach ($methods as $method) {
            $this->route[$method] = [
                'method' => $method,
                'path' => $path,
                'handler' => $handler,
                'controllerAction' => (is_object($handler))
                    ? $this->addControllerActionFromPath(get_class($handler), $path)
                    : null
            ];
        }
    }

    /**
     * @param string $method
     *
     * @return array
     */
    public function getRouteDataByMethod(string $method): array
    {
        return $this->route[$method] ?? [];
    }
}