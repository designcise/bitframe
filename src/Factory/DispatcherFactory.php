<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Factory;

use \Psr\Http\Message\ResponseInterface;

use BitFrame\Dispatcher\DispatcherInterface;
use BitFrame\Factory\DispatcherFactoryInterface;

/**
 * Create the default middleware dispatcher.
 */
class DispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createDispatcher(ResponseInterface $response): DispatcherInterface
    {
        if (class_exists('BitFrame\\Dispatcher\\MiddlewareDispatcher')) {
            return new \BitFrame\Dispatcher\MiddlewareDispatcher($response);
        }

        throw new \RuntimeException('Unable to create a dispatcher; default dispatcher libraries not found.');
    }
}
