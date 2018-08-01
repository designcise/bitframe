<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 *
 * @author    Zend Framework
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace BitFrame\Factory;

use \stdClass;
use \Psr\Http\Message\{ServerRequestFactoryInterface, ResponseFactoryInterface, StreamFactoryInterface, UriFactoryInterface};
use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface, StreamInterface, UriInterface};

/**
 * Creates a new HTTP object, as defined by PSR-7.
 */
class HttpMessageFactory
{
    /** @var ResponseFactoryInterface */
    private static $responseFactory;

    /** @var ServerRequestFactoryInterface */
    private static $serverRequestFactory;

    /** @var StreamFactoryInterface */
    private static $streamFactory;
    
    /** @var UriFactoryInterface */
    private static $uriFactory;

    /**
     * Set a custom Response factory.
     * 
     * @param ResponseFactoryInterface $responseFactory
     */
    public static function setResponseFactory(ResponseFactoryInterface $responseFactory): self
    {
        self::$responseFactory = $responseFactory;
        
        return new static;
    }
    
    /**
     * Set a custom ServerRequest factory.
     * 
     * @param ServerRequestFactoryInterface $serverRequestFactory
     */
    public static function setServerRequestFactory(ServerRequestFactoryInterface $serverRequestFactory): self
    {
        self::$serverRequestFactory = $serverRequestFactory;
        
        return new static;
    }
    
    /**
     * Set a custom Stream factory.
     * 
     * @param StreamFactoryInterface $streamFactory
     */
    public static function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        self::$streamFactory = $streamFactory;
        
        return new static;
    }
    
    /**
     * Set a custom Uri factory.
     * 
     * @param UriFactoryInterface $uriFactory
     */
    public static function setUriFactory(UriFactoryInterface $uriFactory): self
    {
        self::$uriFactory = $uriFactory;
        
        return new static;
    }
    
    /**
     * Creates a Response instance.
     *
     * @param int $code (optional) Http status code
     * @param string $reasonPhrase (optional)
     *
     * @return ResponseInterface
     */
    public static function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if (self::$responseFactory === null) {
            self::$responseFactory = new \BitFrame\Factory\ResponseFactory();
        }

        return self::$responseFactory->createResponse($code, $reasonPhrase);
    }

    /**
     * Creates a new server request.
     *
     * @param string $method (optional)
     * @param string $uri (optional)
     * @param array $serverParams (optional)
     *
     * @return ServerRequestInterface
     */
    public static function createServerRequest(
        string $method = 'GET',
        string $uri = '',
        array $serverParams = []
    ): ServerRequestInterface {
        if (self::$serverRequestFactory === null) {
            self::$serverRequestFactory = new \BitFrame\Factory\ServerRequestFactory();
        }

        return self::$serverRequestFactory->createServerRequest($method, $uri, $serverParams);
    }
    
    /**
     * Create a new server request from server variables.
     *
     * The request method and uri are marshalled from $server.
     *
     * @param array $server
     *
     * @return ServerRequestInterface
     */
    public static function createServerRequestFromArray(
        array $server
    ): ServerRequestInterface {
        if (self::$serverRequestFactory === null) {
            self::$serverRequestFactory = new \BitFrame\Factory\ServerRequestFactory();
        }
        
        $headers = marshalHeadersFromSapi($server);
        $uri = marshalUriFromSapi($server, $headers);
        $method = marshalMethodFromSapi($server);

        return self::$serverRequestFactory->createServerRequest($method, $uri, $server);
    }

    /**
     * Creates a Stream instance with content.
     *
     * @param string $content (optional)
     *
     * @return StreamInterface
     */
    public static function createStream(string $content = ''): StreamInterface
    {
        if (self::$streamFactory === null) {
            self::$streamFactory = new \BitFrame\Factory\StreamFactory();
        }

        return self::$streamFactory->createStream($content);
    }

    /**
     * Creates a Stream instance from file.
     *
     * @param string $filename
     * @param string $mode (optional)
     *
     * @return StreamInterface
     */
    public static function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if (self::$streamFactory === null) {
            self::$streamFactory = new \BitFrame\Factory\StreamFactory();
        }

        return self::$streamFactory->createStreamFromFile($filename, $mode);
    }

    /**
     * Creates a Stream instance from resource returned by fopen.
     *
     * @param resource|null $resource
     *
     * @return StreamInterface
     */
    public static function createStreamFromResource($resource): StreamInterface
    {
        if (self::$streamFactory === null) {
            self::$streamFactory = new \BitFrame\Factory\StreamFactory();
        }

        return self::$streamFactory->createStreamFromResource($resource);
    }

    /**
     * Creates a Uri instance.
     *
     * @param string $uri (optional)
     *
     * @return UriInterface
     */
    public static function createUri(string $uri = ''): UriInterface
    {
        if (self::$uriFactory === null) {
            self::$uriFactory = new \BitFrame\Factory\UriFactory();
        }

        return self::$uriFactory->createUri($uri);
    }
}
