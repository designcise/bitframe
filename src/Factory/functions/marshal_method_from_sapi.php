<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Zend Framework
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace BitFrame\Factory;

/**
 * Retrieve the request method from the SAPI parameters.
 *
 * @param array $server
 * @return string
 */
function marshalMethodFromSapi(array $server)
{
    return isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
}
