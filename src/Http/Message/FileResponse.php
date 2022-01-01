<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
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
    public static function fromPath(string $filePath): self
    {
        return new self($filePath);
    }

    /**
     * @param resource $resource
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public static function fromResource($resource): self
    {
        return (is_resource($resource))
            ? new self($resource)
            : throw new InvalidArgumentException('Resource is invalid.');
    }

    public static function fromStream(StreamInterface $stream): self
    {
        return new self($stream);
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
