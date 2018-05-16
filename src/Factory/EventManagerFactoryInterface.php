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

use BitFrame\EventManager\EventManagerInterface;

/**
 * Representation of an event manager factory.
 */
interface EventManagerFactoryInterface
{
    /**
     * Create a new event manager.
     *
     * @return EventManagerInterface
     */
    public function createEventManager(): EventManagerInterface;
}