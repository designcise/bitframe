<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

use function is_resource;

/**
 * Http response for embedded file.
 */
class FileResponse extends AbstractFileResponse
{
    /**
     * @param string $filePath
     *
     * @return $this
     */
    public static function fromPath(string $filePath): self
    {
        return new static($filePath);
    }

    /**
     * @param resource $resource
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public static function fromResource($resource): self
    {
        if (! is_resource($resource)) {
            throw new InvalidArgumentException('Resource is invalid.');
        }

        return new static($resource);
    }

    /**
     * @param StreamInterface $stream
     *
     * @return $this
     */
    public static function fromStream(StreamInterface $stream): self
    {
        return new static($stream);
    }

    /**
     * @param string|resource|StreamInterface $file
     *
     * @throws InvalidArgumentException
     */
    public function __construct($file)
    {
        parent::__construct($this->createEmbeddedFileResponse($file));
    }
}
