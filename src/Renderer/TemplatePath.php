<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Renderer;

/**
 * A value object describing (optionally) a namespaced path
 * in which templates reside.
 */
class TemplatePath
{
    protected string $path;

    protected ?string $namespace = null;

    /**
     * @param string $path
     * @param null|string $namespace
     */
    public function __construct(string $path, ?string $namespace = null)
    {
        $this->path = $path;
        $this->namespace = $namespace;
    }

    /**
     * @return null|string
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
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
