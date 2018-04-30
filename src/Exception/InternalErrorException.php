<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    CakePHP(tm)
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Exception;

/**
 * Represents an HTTP 500 error.
 */
class InternalErrorException extends \BitFrame\Exception\HttpException
{
    /**
     * @param string $message (optional) If no message is given, defaults to 'Internal Server Error'
     */
    public function __construct($message = null)
    {
        parent::__construct($message ?: 'Internal Server Error', 500);
    }
}