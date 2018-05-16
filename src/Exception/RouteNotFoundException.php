<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Exception;

/**
 * Represents a route not found error.
 */
class RouteNotFoundException extends \BitFrame\Exception\HttpException
{
    /**
     * @param string $route
     */
    public function __construct($route)
    {
        parent::__construct(sprintf('Route "%s" cannot be found', $route), 404);
    }
}