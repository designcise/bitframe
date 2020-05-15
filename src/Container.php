<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame;

use ArrayAccess;
use IteratorAggregate;
use SplObjectStorage;
use Psr\Container\ContainerInterface;
use BitFrame\Exception\{
    ContainerItemNotFoundException,
    ContainerItemFrozenException
};
use TypeError;
use OutOfBoundsException;

use function is_object;
use function get_class;
use function gettype;
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

    /**
     * @param string $id
     *
     * @return bool
     *
     * @see Container::has()
     */
    public function offsetExists($id): bool
    {
        return $this->has($id);
    }

    /**
     * @param string $id
     *
     * @return mixed
     *
     * @see Container::get()
     */
    public function offsetGet($id)
    {
        return $this->get($id);
    }

    /**
     * @param string $id
     * @param mixed $value
     */
    public function offsetSet($id, $value): void
    {
        if (isset($this->frozen[$id])) {
            throw new ContainerItemFrozenException($id);
        }

        $this->bag[$id] = $value;
    }

    /**
     * @param string $id
     *
     * @throws TypeError
     * @throws OutOfBoundsException
     */
    public function offsetUnset($id): void
    {
        if (! $this->has($id)) {
            throw new OutOfBoundsException(sprintf('Offset "%s" does not exist', $id));
        }

        if (isset($this->frozen[$id])) {
            throw new ContainerItemFrozenException($id);
        }

        if (isset($this->instantiated[$id]) && is_object($this->instantiated[$id])) {
            unset($this->factories[$this->bag[$id]]);
        }

        unset($this->bag[$id], $this->frozen[$id]);
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
     *
     * @throws TypeError
     */
    public function has($id): bool
    {
        if (! is_string($id)) {
            throw new TypeError(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($id) ? get_class($id) : gettype($id)
            ));
        }

        return (isset($this->bag[$id]) || array_key_exists($id, $this->bag));
    }

    /**
     * {@inheritDoc}
     *
     * @thorws TypeError
     * @throws ContainerItemNotFoundException
     */
    public function get($id)
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
     * @return iterable
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator(): iterable
    {
        foreach ($this->bag as $key => $value) {
            yield $key => $value;
        }
    }
}
