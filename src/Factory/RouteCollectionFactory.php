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
use BitFrame\Factory\RouteCollectionFactoryInterface;

/**
 * Create the default route collection class.
 */
class RouteCollectionFactory implements RouteCollectionFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createRouteCollection(): RouteCollectionInterface
    {
        if (class_exists('BitFrame\\Router\\RouteCollection')) {
            return new \BitFrame\Router\RouteCollection;
        }

        throw new \RuntimeException('Unable to create a Route Collection; default Route Collection library not found.');
    }
}
