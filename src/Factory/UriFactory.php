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

use \Psr\Http\Message\UriFactoryInterface;
use \Psr\Http\Message\UriInterface;

/**
 * Class to create instances of PSR-7 uri.
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        if (class_exists('Zend\\Diactoros\\Uri')) {
            return new \Zend\Diactoros\Uri($uri);
        }

        if (class_exists('GuzzleHttp\\Psr7\\Uri')) {
            return new \GuzzleHttp\Psr7\Uri($uri);
        }

        throw new \RuntimeException('Unable to create a uri; default PSR-7 uri libraries not found.');
    }
}
