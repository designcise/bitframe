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
use BitFrame\Factory\EventManagerFactoryInterface;

/**
 * Create the default event manager.
 */
class EventManagerFactory implements EventManagerFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createEventManager(): EventManagerInterface
    {
        if (class_exists('BitFrame\\EventManager\\EventManager')) {
            return new \BitFrame\EventManager\EventManager;
        }

        throw new \RuntimeException('Unable to create an Event Manager; default Event Manager library not found.');
    }
}
