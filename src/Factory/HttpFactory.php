<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Factory;

use RuntimeException;
use Psr\Http\Message\{
    ServerRequestInterface,
    RequestInterface,
    ResponseInterface,
    StreamInterface,
    UriInterface,
    UploadedFileInterface
};
use BitFrame\Http\ServerRequestBuilder;
use InvalidArgumentException;

use function array_shift;
use function array_unshift;
use function class_exists;
use function file_get_contents;
use function is_a;
use function is_object;
use function is_string;

use const UPLOAD_ERR_OK;

/**
 * Creates a new HTTP object as defined by PSR-7.
 */
class HttpFactory
{
    /** @var array */
    private static array $factoriesList = [
        'Nyholm\Psr7\Factory\Psr17Factory',
        'GuzzleHttp\Psr7\HttpFactory',
    ];

    /**
     * Add PSR-17 factory creator class.
     *
     * @param string|HttpFactoryInterface $factory
     *
     * @return void
     */
    public static function addFactory($factory): void
    {
        if (! is_a($factory, HttpFactoryInterface::class, true)) {
            throw new InvalidArgumentException(
                'Http factory must either be a string or ' . HttpFactoryInterface::class
            );
        }

        array_unshift(self::$factoriesList, $factory);
    }

    /**
     * @param int $statusCode
     * @param string $reasonPhrase
     *
     * @return ResponseInterface
     */
    public static function createResponse(
        int $statusCode = 200,
        string $reasonPhrase = ''
    ): ResponseInterface {
        $factory = self::getFactory();
        return $factory->createResponse($statusCode, $reasonPhrase);
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     *
     * @return RequestInterface
     */
    public static function createRequest(string $method, $uri): RequestInterface
    {
        return self::getFactory()->createRequest($method, $uri);
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param array $serverParams
     *
     * @return ServerRequestInterface
     */
    public static function createServerRequest(
        string $method,
        $uri,
        array $serverParams = []
    ): ServerRequestInterface {
        return self::getFactory()->createServerRequest($method, $uri, $serverParams);
    }

    /**
     * Create a request from the superglobal values.
     *
     * @param array $server
     * @param array $parsedBody
     * @param array $cookies
     * @param array $files
     * @param resource|string $body
     *
     * @return ServerRequestInterface
     */
    public static function createServerRequestFromGlobals(
        array $server = [],
        array $parsedBody = [],
        array $cookies = [],
        array $files = [],
        $body = ''
    ): ServerRequestInterface {
        return ServerRequestBuilder::fromSapi(
            $server ?: $_SERVER,
            self::getFactory(),
            $parsedBody ?: $_POST ?: [],
            $cookies ?: $_COOKIE ?: [],
            $files ?: $_FILES ?: [],
            $body ?: file_get_contents('php://input') ?: ''
        );
    }

    /**
     * @param string $content
     *
     * @return StreamInterface
     */
    public static function createStream(string $content = ''): StreamInterface
    {
        return self::getFactory()->createStream($content);
    }

    /**
     * @param string $filename
     * @param string $mode
     *
     * @return StreamInterface
     */
    public static function createStreamFromFile(
        string $filename,
        string $mode = 'r'
    ): StreamInterface {
        return self::getFactory()->createStreamFromFile($filename, $mode);
    }

    /**
     * Creates a Stream instance from resource returned by `fopen`.
     *
     * @param resource $resource
     *
     * @return StreamInterface
     */
    public static function createStreamFromResource($resource): StreamInterface
    {
        return self::getFactory()->createStreamFromResource($resource);
    }

    /**
     * @param string $uri
     *
     * @return UriInterface
     */
    public static function createUri(string $uri = ''): UriInterface
    {
        return self::getFactory()->createUri($uri);
    }
    
    /**
     * @param StreamInterface $stream
     * @param null|int $size
     * @param int $error
     * @param null|string $clientFilename
     * @param null|string $clientMediaType
     *
     * @return UploadedFileInterface
     */
    public static function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ): UploadedFileInterface {
        return self::getFactory()->createUploadedFile(
            $stream,
            $size,
            $error,
            $clientFilename,
            $clientMediaType
        );
    }

    /**
     * Returns PSR-17 factory creator class.
     *
     * @return object
     */
    public static function getFactory(): object
    {
        if (! isset(self::$factoriesList[0])) {
            throw new RuntimeException('No supported PSR-17 library found');
        }

        $factory = self::$factoriesList[0];

        if (is_object($factory)) {
            return $factory;
        }
        
        if (is_string($factory) && class_exists($factory)) {
            return self::$factoriesList[0] = new $factory();
        }

        array_shift(self::$factoriesList);
        return self::getFactory();
    }
}
