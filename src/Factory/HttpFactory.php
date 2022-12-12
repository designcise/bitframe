<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Factory;

use Psr\Http\Message\{
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface,
    ServerRequestInterface,
    RequestInterface,
    ResponseInterface,
    StreamInterface,
    UriInterface,
    UploadedFileInterface
};
use BitFrame\Http\ServerRequestBuilder;
use RuntimeException;

use function array_shift;
use function array_unshift;
use function class_exists;
use function file_get_contents;
use function is_object;
use function is_string;

use const UPLOAD_ERR_OK;

/**
 * Creates a new HTTP object as defined by PSR-7.
 */
class HttpFactory
{
    private static array $factoriesList = [
        'Nyholm\Psr7\Factory\Psr17Factory',
        'GuzzleHttp\Psr7\HttpFactory',
    ];

    public static function addFactory(
        RequestFactoryInterface
        & ResponseFactoryInterface
        & ServerRequestFactoryInterface
        & StreamFactoryInterface
        & UploadedFileFactoryInterface
        & UriFactoryInterface $factory
    ): void {
        array_unshift(self::$factoriesList, $factory);
    }

    public static function createResponse(
        int $statusCode = 200,
        string $reasonPhrase = '',
    ): ResponseInterface {
        $factory = self::getFactory();
        return $factory->createResponse($statusCode, $reasonPhrase);
    }

    public static function createRequest(string $method, UriInterface|string $uri): RequestInterface
    {
        return self::getFactory()->createRequest($method, $uri);
    }

    public static function createServerRequest(
        string $method,
        UriInterface|string $uri,
        array $serverParams = [],
    ): ServerRequestInterface {
        return self::getFactory()->createServerRequest($method, $uri, $serverParams);
    }

    /**
     * @param array $server
     * @param array $parsedBody
     * @param array $cookies
     * @param array $files
     * @param resource|string|StreamInterface $body
     *
     * @return ServerRequestInterface
     */
    public static function createServerRequestFromGlobals(
        array $server = [],
        array $parsedBody = [],
        array $cookies = [],
        array $files = [],
        $body = '',
    ): ServerRequestInterface {
        $factory = self::getFactory();

        return ServerRequestBuilder::fromSapi(
            $server ?: $_SERVER,
            $factory,
            $parsedBody ?: $_POST ?: [],
            $cookies ?: $_COOKIE ?: [],
            $files ?: $_FILES ?: [],
            $body ?: file_get_contents('php://input') ?: ''
        );
    }

    public static function createStream(string $content = ''): StreamInterface
    {
        return self::getFactory()->createStream($content);
    }

    public static function createStreamFromFile(
        string $filename,
        string $mode = 'r',
    ): StreamInterface {
        return self::getFactory()->createStreamFromFile($filename, $mode);
    }

    /**
     * @param resource $resource
     *
     * @return StreamInterface
     */
    public static function createStreamFromResource($resource): StreamInterface
    {
        return self::getFactory()->createStreamFromResource($resource);
    }

    public static function createUri(string $uri = ''): UriInterface
    {
        return self::getFactory()->createUri($uri);
    }

    public static function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null,
    ): UploadedFileInterface {
        return self::getFactory()->createUploadedFile(
            $stream,
            $size,
            $error,
            $clientFilename,
            $clientMediaType
        );
    }

    public static function getFactory(): RequestFactoryInterface
        & ResponseFactoryInterface
        & ServerRequestFactoryInterface
        & StreamFactoryInterface
        & UploadedFileFactoryInterface
        & UriFactoryInterface
    {
        $factory = self::$factoriesList[0] ?? throw new RuntimeException('No supported PSR-17 library found');

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
