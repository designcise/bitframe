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

use BitFrame\EventManager\EventInterface;

/**
 * An observable event that is triggered over some action, 
 * and accordingly can be handled by attaching event handlers.
 */
class Event implements EventInterface
{
    /** @var string */
    private $name;
    
    /** @var null|string|object */
    private $target;
    
    /** @var array */
    private $params;
    
    /** @var bool */
    private $propagate;
    
    /**
     * @param string $name
     * @param null|string|object $target (optional)
     * @param array $params (optional)
     * @param bool $propagate (optional)
     */
    public function __construct(
        string $name, 
        $target = null, 
        array $params = [], 
        bool $propagate = false
    ) 
    {
        $this->name = $name;
        $this->target = $target;
        $this->params = $params;
        $this->propagate = $propagate;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setTarget($target): self
    {
        $this->target = $target;
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * {@inheritdoc}
     *
     * @throws \OutOfBoundsException
     */
    public function getParam(string $name)
    {
        if (! isset($this->params[$name]) && $this->params[$name] !== null) {
            throw new \OutOfBoundsException("$name does not exist!");
        }
        
        return $this->params[$name];
    }
    
    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function stopPropagation(bool $flag): self
    {
        $this->propagate = $flag;
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagate;
    }
}