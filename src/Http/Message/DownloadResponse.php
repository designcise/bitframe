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

use function basename;
use function is_resource;
use function is_string;
use function microtime;
use function random_bytes;
use function rawurlencode;
use function sha1;

/**
 * Http response for downloadable file.
 */
class DownloadResponse extends AbstractFileResponse
{
    /**
     * @param string $filePath
     * @param string $serveFilenameAs
     *
     * @return self
     * @throws \Exception
     */
    public static function fromPath(
        string $filePath,
        string $serveFilenameAs = ''
    ): self {
        return new self($filePath, $serveFilenameAs);
    }

    /**
     * @param resource $resource
     * @param string $serveFilenameAs
     *
     * @return self
     *
     * @throws \Exception
     */
    public static function fromResource($resource, string $serveFilenameAs = ''): self
    {
        return (is_resource($resource))
            ? new self($resource, $serveFilenameAs)
            : throw new InvalidArgumentException('Resource is invalid.');
    }

    /**
     * @param StreamInterface $stream
     * @param string $serveFilenameAs
     *
     * @return self
     * @throws \Exception
     */
    public static function fromStream(
        StreamInterface $stream,
        string $serveFilenameAs = ''
    ): self {
        return new self($stream, $serveFilenameAs);
    }

    /**
     * @param string|resource|StreamInterface $file
     * @param string $serveFilenameAs
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function __construct($file, string $serveFilenameAs = '')
    {
        $response = $this->createEmbeddedFileResponse($file);

        if ($serveFilenameAs === '') {
            $serveFilenameAs = (is_string($file))
                ? basename($file)
                : sha1(random_bytes(5) . microtime());
        }

        $disposition = 'attachment; filename=' . $serveFilenameAs
            . "; filename*=UTF-8''" . rawurlencode($serveFilenameAs);

        $response = $response
            ->withHeader('Content-Disposition', $disposition);

        parent::__construct($response);
    }
}
