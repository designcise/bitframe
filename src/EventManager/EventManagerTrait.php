<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    Lievens Benjamin <l.benjamin185@gmail.com>
 * @copyright Copyright (c) 2017 benliev
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\EventManager;

/**
 * Common implementation of an event manager.
 */
trait EventManagerTrait
{
    /** @var array */
    private $listeners = [];
    
    /**
     * {@inheritdoc}
     *
     * @return $this
     *
     * @see \BitFrame\EventManager\EventManagerInterface::attach()
     */
    public function attach(string $eventName, callable $callback, int $priority = 0): self
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        $this->listeners[$eventName][] = compact('callback', 'priority');
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return bool
     *
     * @see \BitFrame\EventManager\EventManagerInterface::detach()
     */
    public function detach(string $eventName, callable $callback): bool
    {
        if (isset($this->listeners[$eventName])) {
            
            $oldCount = count($this->listeners[$eventName]);
            
            $this->listeners[$eventName] = array_filter(
                $this->listeners[$eventName],
                function ($listener) use ($callback) {
                    return $listener['callback'] !== $callback;
                }
            );
            
            // returns true if one or more items have been filtered
            return ($oldCount > count($this->listeners[$eventName]));
        }
        
        return false;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return $this
     *
     * @see \BitFrame\EventManager\EventManagerInterface::clearListeners()
     */
    public function clearListeners(?string $eventName = null): self
    {
        if ($eventName !== null) {
            unset($this->listeners[$eventName]);
        } else {
            $this->listeners = [];
        }
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     *
     * @see \BitFrame\EventManager\EventManagerInterface::trigger()
     */
    public function trigger($event, $target = null, $argv = []): void
    {
        if (is_string($event)) {
            $event = new Event($event);
            $event->stopPropagation(false);
        } elseif (! ($event instanceof \BitFrame\EventManager\EventInterface)) {
            throw new \InvalidArgumentException('Invalid event type');
        }
        
        $evtName = $event->getName();
        
        // event has listeners?
        if (! empty($this->getListeners($evtName))) {
            if ($target !== null) {
                $event->setTarget($target);
            }

            // combine supplied params with ones in the Event object
            if (! empty($argv)) {
                // if same string keys, then the later value overwrites the previous one
                $event->setParams(array_merge($event->getParams(), (array)$argv));
            }

            // sort as per the priority
            usort(
                $this->listeners[$evtName],
                function ($a, $b) {
                    // case 1: return 0 if values on either side are equal (e.g. 1 <=> 1)
                    // case 2: return 1 if value on the left is greater (e.g. 3 <=> 4)
                    // case 3: return -1 if the value on the right is greater (e.g. 4 <=> 3)
                    return $a['priority'] <=> $b['priority'];
                }
            );
            
            // run all listeners attached to this event
            foreach ($this->getListeners($evtName) as ['callback' => $callback]) {
                call_user_func($callback, $event);
                
                // prevent event from bubbling up to other handlers
                if ($event->isPropagationStopped()) {
                    // halt
                    return;
                }
            }
        }
    }
    
     /**
     * {@inheritdoc}
     *
     * @see \BitFrame\EventManager\EventManagerInterface::getListeners()
     */
    public function getListeners($eventName): array
    {
        return isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : [];
    }
}