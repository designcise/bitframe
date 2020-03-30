<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Represents a container item frozen error.
 */
class ContainerItemFrozenException extends RuntimeException implements ContainerExceptionInterface
{
    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        parent::__construct(\sprintf('"%s" is frozen and cannot be modified', $id));
    }
}
