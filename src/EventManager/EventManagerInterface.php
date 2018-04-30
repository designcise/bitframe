<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    PHP Framework Interoperability Group
 * @copyright Copyright (c) 2012 PHP Framework Interoperability Group
 * @see       https://github.com/php-fig/fig-standards/blob/master/proposed/event-manager.md
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\EventManager;

/**
 * Representation of an event manager.
 */
interface EventManagerInterface
{
    /**
	 * Attaches a listener to an event.
	 *
     * @param string $eventName
     * @param callable $callback
	 * @param int $priority (optional)
     */
    public function attach(string $eventName, callable $callback, int $priority = 0);
	
    /**
	 * Detaches a listener from an event.
	 *
     * @param string $eventName
     * @param callable $callback
	 *
	 * @return bool
     */
    public function detach(string $eventName, callable $callback): bool;
	
    /**
	 * Clear all listeners (for a given event).
	 *
     * @param string|null $eventName (optional)
     */
    public function clearListeners(?string $eventName = null);
	
    /**
	 * Trigger an event.
	 *
	 * Can accept an Event object or will create one if not passed.
	 *
     * @param string|\BitFrame\Event\Event $event
	 * @param null|string|object $target (optional)
	 * @param array|object $argv (optional)
     */
    public function trigger($event, $target = null, $argv = []);
	
    /**
	 * Get all event's listeners.
	 *
     * @param string $eventName
	 *
     * @return array
     */
    public function getListeners($eventName): array;
}