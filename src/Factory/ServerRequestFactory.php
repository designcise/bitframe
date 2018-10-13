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

use \Psr\Http\Message\ServerRequestFactoryInterface;
use \Psr\Http\Message\{ServerRequestInterface, UriInterface};

use BitFrame\Message\RequestTrait;
use BitFrame\Factory\HttpMessageFactory;

/**
 * Creates instances of PSR-7 http server request.
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        $headers = marshalHeadersFromSapi($serverParams);
        $version = marshalProtocolVersionFromSapi($serverParams);
        
        $uri = ($uri instanceof UriInterface) ? $uri : (
            (is_string($uri)) ? HttpMessageFactory::createUri($uri) : marshalUriFromSapi($serverParams, $headers)
        );
        
        // normalized uploaded files as per PSR-7 standard
        // @see https://www.php-fig.org/psr/psr-7/#16-uploaded-files
        $files = normalizeUploadedFiles($_FILES);
        
        $cookies = (null === $_COOKIE && array_key_exists('cookie', $headers)) ? parseCookieHeader($headers['cookie']) : $_COOKIE;
        
        if (class_exists('Zend\\Diactoros\\ServerRequest')) {
            $serverParams  = \Zend\Diactoros\normalizeServer($serverParams);
            parse_str($uri->getQuery(), $queryStrArray);
            
            return new class($serverParams, $files, $uri, $method, $headers, $_COOKIE, $queryStrArray, $_POST, $version) extends \Zend\Diactoros\ServerRequest {
                use RequestTrait;
                
                public function __construct(
                    array $serverParams,
                    array $uploadedFiles,
                    $uri,
                    $method,
                    array $headers,
                    array $cookies,
                    array $queryParams,
                    $parsedBody,
                    $protocol
                ) {
                    parent::__construct(
                        $serverParams,
                        $uploadedFiles, 
                        $uri,
                        $method,
                        new \Zend\Diactoros\Stream(fopen('php://temp', 'r+')),
                        $headers,
                        $cookies,
                        $queryParams, // query
                        $parsedBody, // body
                        $protocol
                    );
                }
            };
        }

        if (class_exists('GuzzleHttp\\Psr7\\ServerRequest')) {
            return new class($method, $uri, $headers, $version, $serverParams) extends \GuzzleHttp\Psr7\ServerRequest {
                use RequestTrait;
                
                public function __construct(
                    $method,
                    $uri,
                    array $headers,
                    $version,
                    array $serverParams
                ) {
                    parent::__construct(
                        $method, 
                        $uri, 
                        $headers, 
                        new \GuzzleHttp\Psr7\LazyOpenStream('php://input', 'r+'), 
                        $version, 
                        $serverParams
                    );
                }
            };
        }

        throw new \RuntimeException('Unable to create a server request; default PSR-7 server request libraries not found.');
    }
}
