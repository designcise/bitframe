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
 * Represents an HTTP 400 error.
 */
class BadRequestException extends \BitFrame\Exception\HttpException
{
    /**
     * @param string $message (optional) If no message is given, defaults to 'Bad Request'
     */
    public function __construct(string $message = null)
    {
        parent::__construct($message ?: 'Bad Request', 400);
    }
}