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
 * Represents an HTTP 401 error.
 */
class UnauthorizedException extends \BitFrame\Exception\HttpException
{
    /**
     * @param string $message (optional) If no message is given, defaults to 'Unauthorized'
     */
    public function __construct($message = null)
    {
        parent::__construct($message ?: 'Unauthorized', 401);
    }
}