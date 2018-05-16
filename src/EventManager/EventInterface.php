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
 * Representation of an event.
 */
interface EventInterface
{
    /**
     * Get event name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get target/context from which event was triggered.
     *
     * @return null|string|object
     */
    public function getTarget();

    /**
     * Get parameters passed to the event.
     *
     * @return array
     */
    public function getParams(): array;

    /**
     * Get a single parameter by name.
     *
     * @param  string $name
     *
     * @return mixed
     */
    public function getParam(string $name);

    /**
     * Set the event name.
     *
     * @param  string $name
     */
    public function setName(string $name);

    /**
     * Set the event target.
     *
     * @param  null|string|object $target
     */
    public function setTarget($target);

    /**
     * Set event parameters.
     *
     * @param  array $params
     */
    public function setParams(array $params);

    /**
     * Indicate whether or not to stop propagating this event.
     *
     * @param  bool $flag
     */
    public function stopPropagation(bool $flag);

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped(): bool;
}