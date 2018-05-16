<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Factory;

use BitFrame\Router\RouteCollectionInterface;

/**
 * Representation of route collection factory.
 */
interface RouteCollectionFactoryInterface
{
    /**
     * Create a new router collection.
     *
     * @return RouteCollectionInterface
     */
    public function createRouteCollection(): RouteCollectionInterface;
}