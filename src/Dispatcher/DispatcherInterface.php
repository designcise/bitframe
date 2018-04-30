<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Dispatcher;

use \Psr\Http\Server\RequestHandlerInterface;

/**
 * Representation of a middleware dispatcher.
 */
interface DispatcherInterface extends RequestHandlerInterface
{
	/**
     * Add a middleware.
     *
	 * @param callable|\Psr\Http\Server\MiddlewareInterface|array $middleware
     */
	public function addMiddleware($middleware);
	
	/**
     * Reset pending and processed middleware queues.
     */
	public function clear();
	
	/**
     * Get all pending middleware.
	 *
	 * @param string|null $chain
	 *
     * @return array
     */
    public function getPendingMiddleware(): array;
	
	/**
     * Get all processed middleware.
	 *
     * @return array
     */
	public function getProcessedMiddleware(): array;
	
	/**
	 * Check if dispatcher is running (processing middleware).
	 *
	 * @return bool
	 */
	public function isRunning(): bool;
	
	/**
	 * Check if there are pending middleware.
	 *
	 * @return bool
	 */
	public function hasMiddleware(): bool;
}