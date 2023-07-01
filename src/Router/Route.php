<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Router;

use Attribute;

/**
 * Single route.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * @param array|string $methods
     * @param string $path
     */
    public function __construct(
        protected array|string $methods,
        protected string $path,
    ) {}

    public function getMethods(): array|string
    {
        return $this->methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
