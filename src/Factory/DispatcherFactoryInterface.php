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

/**
 * Representation of an dispatcher factory.
 */
interface DispatcherFactoryInterface
{
    /**
     * Create a new middleware dispatcher.
     *
     * @param ResponseInterface $response
     *
     * @return DispatcherInterface
     */
    public function createDispatcher(ResponseInterface $response): DispatcherInterface;
}
