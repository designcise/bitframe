<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Exception;

/**
 * Represents an invalid middleware error.
 */
class InvalidMiddlewareException extends \RuntimeException
{
	/**
     * @param string $message
     * @param int $code (optional) Status code, defaults to 500
     */
    public function __construct($message, $code = 500)
    {
        parent::__construct($message, $code);
    }
}
