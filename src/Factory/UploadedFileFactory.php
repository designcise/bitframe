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

use \Psr\Http\Message\UploadedFileFactoryInterface;
use \Psr\Http\Message\UploadedFileInterface;

/**
 * Class to create instances of PSR-7 uploaded file.
 */
class UploadedFileFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        if (class_exists('Zend\\Diactoros\\UploadedFile')) {
            return new \Zend\Diactoros\UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
        }

        if (class_exists('GuzzleHttp\\Psr7\\UploadedFile')) {
            return new \GuzzleHttp\Psr7\UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
        }

        throw new \RuntimeException('Unable to create an uploaded file; default PSR-7 uploaded file libraries not found.');
    }
}
