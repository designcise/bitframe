<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame;

use ArrayAccess;
use IteratorAggregate;
use SplObjectStorage;
use Traversable;
use Psr\Container\ContainerInterface;
use BitFrame\Exception\{ContainerItemNotFoundException, ContainerItemFrozenException};
use TypeError;
use OutOfBoundsException;

use function is_object;
use function sprintf;
use function is_callable;
use function array_key_exists;

/**
 * Simple PHP dependency injection container.
 */
class Container implements ContainerInterface, ArrayAccess, IteratorAggregate
{
    private array $bag = [];

    private array $frozen = [];

    private array $instantiated = [];

    private SplObjectStorage $factories;

    public function __construct()
    {
        $this->factories = new SplObjectStorage();
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $strOffset = (string) $offset;

        if (isset($this->frozen[$strOffset])) {
            throw new ContainerItemFrozenException($strOffset);
        }

        $this->bag[$strOffset] = $value;
    }

    /**
     * @param mixed $offset
     *
     * @throws OutOfBoundsException
     */
    public function offsetUnset(mixed $offset): void
    {
        $strOffset = (string) $offset;

        if (! $this->has($strOffset)) {
            throw new OutOfBoundsException(
                sprintf('Offset "%s" does not exist', $strOffset)
            );
        }

        if (isset($this->frozen[$strOffset])) {
            throw new ContainerItemFrozenException($strOffset);
        }

        if (isset($this->instantiated[$strOffset]) && is_object($this->instantiated[$strOffset])) {
            unset($this->factories[$this->bag[$strOffset]]);
        }

        unset($this->bag[$strOffset], $this->frozen[$strOffset]);
    }

    /**
     * @param string $id
     *
     * @return $this
     *
     * @throws TypeError
     */
    public function freeze(string $id): self
    {
        if ($this->has($id)) {
            $this->frozen[$id] = true;
        }

        return $this;
    }

    public function factory(object $factory): object
    {
        $this->factories->attach($factory);

        return $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return (isset($this->bag[$id]) || array_key_exists($id, $this->bag));
    }

    /**
     * {@inheritDoc}
     *
     * @throws ContainerItemNotFoundException
     */
    public function get(string $id)
    {
        if (! $this->has($id)) {
            throw new ContainerItemNotFoundException($id);
        }

        if (isset($this->instantiated[$id])) {
            if (isset($this->factories[$this->bag[$id]])) {
                return $this->bag[$id]($this);
            }

            return $this->instantiated[$id];
        }

        if (is_callable($this->bag[$id])) {
            return $this->instantiated[$id] = $this->bag[$id]($this);
        }

        return $this->bag[$id];
    }

    /**
     * {@inheritdoc}
     *
     * @return Traversable
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator(): Traversable
    {
        foreach ($this->bag as $key => $value) {
            yield $key => $value;
        }
    }
}
