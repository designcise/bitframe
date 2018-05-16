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

use \Interop\Http\Factory\ServerRequestFactoryInterface;
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
     */
    public function createServerRequest($method, $uri): ServerRequestInterface
    {
        return self::create([], $method, $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function createServerRequestFromArray(array $server): ServerRequestInterface
    {
        $headers = self::marshalHeaders($server);
        $uri = self::marshalUriFromServer($server, $headers);
        $method = HttpMessageFactory::get('REQUEST_METHOD', $server, 'GET');
        
        return self::create($server, $method, $uri);
    }

    /**
     * Create a Server request.
     *
     * @param array  $server
     * @param string $method
     * @param string $uri
     *
     * @return ServerRequestInterface
     */
    private static function create(array $server, $method, $uri): ServerRequestInterface
    {
        $headers = self::marshalHeaders($server);
        $version = static::marshalProtocolVersion($server);
        
        $uri = ($uri instanceof UriInterface) ? $uri : (
            (is_string($uri)) ? HttpMessageFactory::createUri($uri) : self::marshalUriFromServer($server, $headers)
        );
        
        if (class_exists('Zend\\Diactoros\\ServerRequest')) {
            $server  = \Zend\Diactoros\ServerRequestFactory::normalizeServer($server ?: $_SERVER);
            parse_str($uri->getQuery(), $queryStrArray);

            return new class($server, $_FILES, $uri, $method, $headers, $_COOKIE, $queryStrArray, $_POST, $version) extends \Zend\Diactoros\ServerRequest {
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
            return new class($method, $uri, $headers, $version, $server) extends \GuzzleHttp\Psr7\ServerRequest {
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
    
    /**
     * Strip the query string from a path.
     *
     * @param mixed $path
     *
     * @return string
     *
     * @see: \Zend\Diactoros\ServerRequestFactory::stripQueryString()
     */
    public static function stripQueryString($path): string
    {
        if (($qpos = strpos($path, '?')) !== false) {
            return substr($path, 0, $qpos);
        }
        return $path;
    }
    
    /**
     * Marshal headers from $_SERVER.
     *
     * @param array $server
     *
     * @return array
     *
     * @see: \Zend\Diactoros\ServerRequestFactory::marshalHeaders()
     */
    public static function marshalHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            // Apache prefixes environment variables with REDIRECT_
            // if they are added by rewrite rules
            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);

                // We will not overwrite existing variables with the
                // prefixed versions, though
                if (array_key_exists($key, $server)) {
                    continue;
                }
            }

            if ($value && strpos($key, 'HTTP_') === 0) {
                $name = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;
                continue;
            }

            if ($value && strpos($key, 'CONTENT_') === 0) {
                $name = 'content-' . strtolower(substr($key, 8));
                $headers[$name] = $value;
                continue;
            }
        }

        return $headers;
    }
    
    /**
     * Marshal the URI from the $_SERVER array and headers.
     *
     * @param array $server
     * @param array $headers
     *
     * @return Uri
     *
     * @see: \Zend\Diactoros\ServerRequestFactory::marshalUriFromServer()
     */
    public static function marshalUriFromServer(array $server, array $headers)
    {
        $uri = HttpMessageFactory::createUri('');

        // URI scheme
        $scheme = 'http';
        $https  = HttpMessageFactory::get('HTTPS', $server);
        if (($https && 'off' !== $https)
            || self::getHeader('x-forwarded-proto', $headers, false) === 'https'
        ) {
            $scheme = 'https';
        }
        if (! empty($scheme)) {
            $uri = $uri->withScheme($scheme);
        }

        // Set the host
        $accumulator = (object) ['host' => '', 'port' => null];
        self::marshalHostAndPortFromHeaders($accumulator, $server, $headers);
        $host = $accumulator->host;
        $port = $accumulator->port;
        if (! empty($host)) {
            $uri = $uri->withHost($host);
            if (! empty($port)) {
                $uri = $uri->withPort($port);
            }
        }

        // URI path
        $path = self::marshalRequestUri($server);
        $path = self::stripQueryString($path);

        // URI query
        $query = '';
        if (isset($server['QUERY_STRING'])) {
            $query = ltrim($server['QUERY_STRING'], '?');
        }

        // URI fragment
        $fragment = '';
        if (strpos($path, '#') !== false) {
            list($path, $fragment) = explode('#', $path, 2);
        }

        return $uri
            ->withPath($path)
            ->withFragment($fragment)
            ->withQuery($query);
    }
    
    /**
     * Detect the base URI for the request.
     *
     * Looks at a variety of criteria in order to attempt to autodetect a base
     * URI, including rewrite URIs, proxy URIs, etc.
     *
     * From ZF2's Zend\Http\PhpEnvironment\Request class
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * @param array $server
     *
     * @return string
     *
     * @see: \Zend\Diactoros\ServerRequestFactory::marshalRequestUri()
     */
    public static function marshalRequestUri(array $server): string
    {
        // IIS7 with URL Rewrite: make sure we get the unencoded url
        // (double slash problem).
        $iisUrlRewritten = HttpMessageFactory::get('IIS_WasUrlRewritten', $server);
        $unencodedUrl    = HttpMessageFactory::get('UNENCODED_URL', $server, '');
        if ('1' == $iisUrlRewritten && ! empty($unencodedUrl)) {
            return $unencodedUrl;
        }

        $requestUri = HttpMessageFactory::get('REQUEST_URI', $server);

        // Check this first so IIS will catch.
        $httpXRewriteUrl = HttpMessageFactory::get('HTTP_X_REWRITE_URL', $server);
        if ($httpXRewriteUrl !== null) {
            $requestUri = $httpXRewriteUrl;
        }

        // Check for IIS 7.0 or later with ISAPI_Rewrite
        $httpXOriginalUrl = HttpMessageFactory::get('HTTP_X_ORIGINAL_URL', $server);
        if ($httpXOriginalUrl !== null) {
            $requestUri = $httpXOriginalUrl;
        }

        if ($requestUri !== null) {
            return preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
        }

        $origPathInfo = HttpMessageFactory::get('ORIG_PATH_INFO', $server);
        if (empty($origPathInfo)) {
            return '/';
        }

        return $origPathInfo;
    }
    
    /**
     * Search for a header value.
     *
     * Does a case-insensitive search for a matching header.
     *
     * If found, it is returned as a string, using comma concatenation.
     *
     * If not, the $default is returned.
     *
     * @param string $header
     * @param array $headers
     * @param mixed $default (optional)
     *
     * @return string
     *
     * @see: \Zend\Diactoros\ServerRequestFactory::getHeader()
     */
    public static function getHeader($header, array $headers, $default = null): string
    {
        $header  = strtolower($header);
        $headers = array_change_key_case($headers, CASE_LOWER);
        if (array_key_exists($header, $headers)) {
            $value = is_array($headers[$header]) ? implode(', ', $headers[$header]) : $headers[$header];
            return $value;
        }

        return $default;
    }
    
    /**
     * Marshal the host and port from HTTP headers and/or the PHP environment.
     *
     * @param stdClass $accumulator
     * @param array $server
     * @param array $headers
     * 
     * @return void
     *
     * @see: \Zend\Diactoros\ServerRequestFactory::marshalHostAndPortFromHeaders()
     */
    public static function marshalHostAndPortFromHeaders(stdClass $accumulator, array $server, array $headers): void
    {
        if (self::getHeader('host', $headers, false)) {
            self::marshalHostAndPortFromHeader($accumulator, self::getHeader('host', $headers));
            return;
        }

        if (! isset($server['SERVER_NAME'])) {
            return;
        }

        $accumulator->host = $server['SERVER_NAME'];
        if (isset($server['SERVER_PORT'])) {
            $accumulator->port = (int) $server['SERVER_PORT'];
        }

        if (! isset($server['SERVER_ADDR']) || ! preg_match('/^\[[0-9a-fA-F\:]+\]$/', $accumulator->host)) {
            return;
        }

        // Misinterpreted IPv6-Address
        // Reported for Safari on Windows
        self::marshalIpv6HostAndPort($accumulator, $server);
    }
    
    /**
     * Marshal the host and port from the request header.
     *
     * @param stdClass $accumulator
     * @param string|array $host
     *
     * @see: \Zend\Diactoros\ServerRequestFactory::marshalHostAndPortFromHeader()
     */
    private static function marshalHostAndPortFromHeader(stdClass $accumulator, $host)
    {
        if (is_array($host)) {
            $host = implode(', ', $host);
        }

        $accumulator->host = $host;
        $accumulator->port = null;

        // works for regname, IPv4 & IPv6
        if (preg_match('|\:(\d+)$|', $accumulator->host, $matches)) {
            $accumulator->host = substr($accumulator->host, 0, -1 * (strlen($matches[1]) + 1));
            $accumulator->port = (int) $matches[1];
        }
    }
    
    /**
     * Marshal host/port from misinterpreted IPv6 address.
     *
     * @param stdClass $accumulator
     * @param array $server
     *
     * @see: \Zend\Diactoros\ServerRequestFactory::marshalIpv6HostAndPort()
     */
    private static function marshalIpv6HostAndPort(stdClass $accumulator, array $server)
    {
        $accumulator->host = '[' . $server['SERVER_ADDR'] . ']';
        $accumulator->port = $accumulator->port ?: 80;
        if ($accumulator->port . ']' === substr($accumulator->host, strrpos($accumulator->host, ':') + 1)) {
            // The last digit of the IPv6-Address has been taken as port
            // Unset the port so the default port can be used
            $accumulator->port = null;
        }
    }
    
    /**
     * Return HTTP protocol version (X.Y)
     *
     * @param array $server
     *
     * @return string
     */
    private static function marshalProtocolVersion(array $server): string
    {
        if (! isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnexpectedValueException(sprintf(
                'Unrecognized protocol version (%s)',
                $server['SERVER_PROTOCOL']
            ));
        }

        return $matches['version'];
    }
}
