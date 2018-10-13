<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Zend Diactoros
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace BitFrame\Factory;

use UnexpectedValueException;

use function preg_match;

/**
 * Return HTTP protocol version (X.Y) as discovered within a `$_SERVER` array.
 *
 * @param array $server
 * @return string
 * @throws UnexpectedValueException if the $server['SERVER_PROTOCOL'] value is
 *     malformed.
 */
function marshalProtocolVersionFromSapi(array $server)
{
    if (! isset($server['SERVER_PROTOCOL'])) {
        return '1.1';
    }

    if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
        throw new UnexpectedValueException(sprintf(
            'Unrecognized protocol version (%s)',
            $server['SERVER_PROTOCOL']
        ));
    }

    return $matches['version'];
}
