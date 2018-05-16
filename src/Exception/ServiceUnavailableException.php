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
 * Represents an HTTP 503 error.
 */
class ServiceUnavailableException extends \BitFrame\Exception\HttpException
{
    /**
     * @param string $message (optional) If no message is given, defaults to 'Service Unavailable'
     */
    public function __construct($message = null)
    {
        parent::__construct($message ?: 'Service Unavailable', 503);
    }
}