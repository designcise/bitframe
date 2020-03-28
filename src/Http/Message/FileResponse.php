<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

use Psr\Http\Message\{ResponseInterface, StreamInterface};
use InvalidArgumentException;

use function basename;
use function file_exists;
use function is_resource;
use function is_string;
use function microtime;
use function mime_content_type;
use function random_bytes;
use function rawurlencode;
use function sha1;

/**
 * Http response for embedded/downloadable file.
 */
class FileResponse extends Response
{
    /** @var string|resource|StreamInterface */
    private $file;

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
        parent::__construct();

        $this->file = $file;
        $isFilePath = is_string($file);

        if ($isFilePath && ! file_exists($file)) {
            throw new InvalidArgumentException("File \"{$file}\" does not exist.");
        }

        $mimeType = ($isFilePath) ? mime_content_type($file) : 'application/octet-stream';
        $stream = $this->getFileAsStream($file);

        $this->response = $this->response
            ->withHeader('Content-Type', $mimeType)
            ->withBody($stream);
    }

    /**
     * @param string $serveFilenameAs
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function withDownload(string $serveFilenameAs = ''): ResponseInterface
    {
        if ($serveFilenameAs === '') {
            $serveFilenameAs = (is_string($this->file))
                ? basename($this->file)
                : sha1(random_bytes(5) . microtime());
        }

        $disposition = 'attachment; filename=' . $serveFilenameAs
            . "; filename*=UTF-8''" . rawurlencode($serveFilenameAs);

        $this->response = $this->response
            ->withHeader('Content-Disposition', $disposition);
        
        return $this;
    }

    /**
     * @param string|resource|StreamInterface $file
     *
     * @return StreamInterface
     */
    private function getFileAsStream($file): StreamInterface
    {
        if (is_string($file)) {
            $file = $this->factory->createStreamFromFile($file, 'r');
        } elseif (is_resource($file)) {
            $file = $this->factory->createStreamFromResource($file);
        }

        return $file;
    }
}
