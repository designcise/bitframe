<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    Phil Bennett <philipobenito@gmail.com>
 * @copyright Copyright (c) 2017 Phil Bennett <philipobenito@gmail.com>
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Router;

/**
 * Provides common router-related methods.
 */
trait RouteConditionTrait
{
    /** @var string */
    protected $host;
	
    /** @var string */
    protected $scheme;
	
    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }
	
    /**
     * Set the host.
     *
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host): self
    {
        $this->host = $host;
		
        return $this;
    }
	
    /**
     * Get the scheme.
     *
     * @return string
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }
	
    /**
     * Set the scheme.
     *
     * @param string $scheme
     *
     * @return $this
     */
    public function setScheme($scheme): self
    {
        $this->scheme = $scheme;
		
        return $this;
    }
}