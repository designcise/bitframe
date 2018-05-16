<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Data;

use \OutOfBoundsException;

/**
 * Common implementation of the \ArrayAccess interface methods.
 */
trait ApplicationDataTrait
{
    /**
     * {@inheritdoc}
     *
     * @see: \ArrayAccess::offsetSet()
     */
    public function offsetSet($key, $value)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws OutOfBoundsException
     *
     * @see: \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($key)
    {
        // special case: isset returns false for null values
        if (! isset($this->data[$key]) && $this->data[$key] !== null) {
            throw new OutOfBoundsException("$key does not exist!");
        }
        
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     *
     * @see: \ArrayAccess::offsetExists()
     */
    public function offsetExists($key): bool
    {
        return (array_key_exists($key, $this->data));
    }
    
    /**
     * {@inheritdoc}
     *
     * @throws OutOfBoundsException
     *
     * @see: \ArrayAccess::offsetGet()
     */
    public function offsetGet($key)
    {
        // special case: isset returns false for null values
        if (! isset($this->data[$key]) && $this->data[$key] !== null) {
            throw new OutOfBoundsException("$key does not exist!");
        }
        
        return $this->data[$key];
    }
    
    
    /**
     * Get stored application data.
     *
     * @return mixed
     */
    abstract public function getData();
}