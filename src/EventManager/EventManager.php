<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\EventManager;

/**
 * Holds all the listeners for a particular event and
 * methods to manage an event.
 */
class EventManager implements EventManagerInterface
{
    use EventManagerTrait;
}