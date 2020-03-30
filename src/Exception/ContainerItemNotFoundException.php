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

use RuntimeException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Represents a container item not found error.
 */
class ContainerItemNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        parent::__construct(\sprintf('"%s" not found in container', $id));
    }
}
