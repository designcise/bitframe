<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    CakePHP(tm)
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Exception;

/**
 * Not Implemented Exception - used when an API 
 * method is not implemented.
 */
class NotImplementedException extends \BitFrame\Exception\HttpException
{
    /**
     * @param string $method Name of the method
     */
    public function __construct($method)
    {
        parent::__construct(sprintf('%s is not implemented.', $method), 501);
    }
}