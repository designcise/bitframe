<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Router;

use function ltrim;
use function substr;

/**
 * Group multiple routes together under the same prefix.
 */
class RouteGroup extends AbstractRouter
{
    protected string $prefix;

    /** @var callable */
    protected $handler;

    protected AbstractRouter $routeMapper;

    public function __construct(
        string $prefix,
        callable $handler,
        AbstractRouter $routeMapper
    ) {
        $this->prefix = '/' . ltrim($prefix, '/');
        $this->handler = $handler;
        $this->routeMapper = $routeMapper;

        ($this->handler)($this);
    }

    /**
     * {@inheritdoc}
     */
    public function map($methods, string $path, $handler)
    {
        if ($path === '' || $path === '/') {
            $path = '';
        } else {
            $path = ((substr($this->prefix, -1) === '/') ? '' : '/')
                . ltrim($path, '/');
        }

        $this->routeMapper
            ->map($methods, $this->prefix . $path, $handler);
    }
}
