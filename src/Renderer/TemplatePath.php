<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 *
 * @author    Zend Framework
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-template/blob/master/LICENSE.md New BSD License
 */

namespace BitFrame\Renderer;

/**
 * A value object describing a (optionally) namespaced path 
 * in which templates reside.
 */
class TemplatePath
{
    /** @var string */
    protected $path;

    /** @var null|string */
    protected $namespace;

    /**
     * @param string $path
     * @param null|string $namespace (optional)
     */
    public function __construct(string $path, ?string $namespace = null)
    {
        $this->path = $path;
        $this->namespace = $namespace;
    }

    /**
     * Get the namespace.
     *
     * @return null|string
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Casts to string by returning the path only.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->path;
    }
}
