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

use BitFrame\EventManager\EventManagerInterface;

/**
 * This trait exposes the EventManager to any 'aware' class.
 */
trait EventManagerAwareTrait
{
    /**
     * {@inheritdoc}
     *
     * @return $this
     *
     * @see EventManagerInterface::attach()
     */
    public function attach(string $eventName, callable $callback, int $priority = 0): self
    {
        $this->getEventManager()->attach($eventName, $callback, $priority);
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     *
     * @see EventManagerInterface::detach()
     */
    public function detach(string $eventName, callable $callback): bool 
    {
        return $this->getEventManager()->detach($eventName, $callback);
    }
    
    /**
     * {@inheritdoc}
     *
     * @return $this
     *
     * @see EventManagerInterface::clearListeners()
     */
    public function clearListeners(?string $eventName = null): self
    {
        $this->getEventManager()->clearListeners($eventName);
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return $this
     *
     * @see EventManagerInterface::trigger()
     */
    public function trigger($event, $target = null, $argv = []): self 
    {
        $this->getEventManager()->trigger($event, $target, $argv);
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     *
     * @see EventManagerInterface::getListeners()
     */
    public function getListeners($eventName): array
    {
        return $this->getEventManager()->getListeners($eventName);
    }
    
    /**
     * Set the event manager object.
     *
     * @param EventManagerInterface $eventManager
     *
     * @return $this
     */
    public function setEventManager(EventManagerInterface $eventManager): self
    {
        $this->eventManager = $eventManager;
        
        return $this;
    }
    
    /**
     * Get the event manager object.
     *
     * @return EventManagerInterface
     */
    abstract public function getEventManager(): EventManagerInterface;
}