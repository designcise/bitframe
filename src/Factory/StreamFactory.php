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

use \Interop\Http\Factory\StreamFactoryInterface;

/**
 * Class to create instances of PSR-7 streams.
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream($content = '')
    {
        $stream = $this->createStreamFromFile('php://temp', 'r+');
        $stream->write($content);

        return $stream;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \BitFrame\Exception\FileNotReadableException
     */
    public function createStreamFromFile($filename, $mode = 'r')
    {
        if (! file_exists($filename)) {
            throw new \BitFrame\Exception\FileNotReadableException($filename);
        }
        
        return $this->createStreamFromResource(fopen($filename, $mode));
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource)
    {
        if (class_exists('Zend\\Diactoros\\Stream')) {
            return new \Zend\Diactoros\Stream($resource);
        }

        if (class_exists('GuzzleHttp\\Psr7\\Stream')) {
            return new \GuzzleHttp\Psr7\Stream($resource);
        }

        throw new \RuntimeException('Unable to create a stream; default PSR-7 stream libraries not found.');
    }
}
