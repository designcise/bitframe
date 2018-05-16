<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    Slim Framework (https://slimframework.com)
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Message;

use \Psr\Http\Message\{ResponseInterface, UriInterface};

use BitFrame\Factory\HttpMessageFactory;

/**
 * Provides extended, proprietary, functionality for 
 * the PSR-7 Http Request interface.
 */
trait RequestTrait
{
    /**
     * Get the URL endpoints.
     *
     * If the specified $index is not found, an error is not raised,
     * instead [] is returned.
     *
     * Note: This method is not part of the PSR standard.
     *
     * @return string|array|null
     */
    public function getEndpoints()
    {
        $endpoints = explode('/', trim($this->getUri()->getPath(), '/'));
        return ((array)$endpoints);
    }
    
    /**
     * Get a URL endpoint.
     *
     * Note:
     *
     * - This method is not part of the PSR standard.
     *
     * - $index = 1 means the first endpoint (as opposed to 0 used 
     *   by array indexes).
     *
     * @param int $index
     * @param mixed $default (optional)
     *
     * @return string|array|null
     */
    public function getEndpoint(int $index, $default = null)
    {
        $endpoints = explode('/', trim($this->getUri()->getPath(), '/'));
        return ((isset($endpoints[$index-1])) ? $endpoints[$index-1] : $default);
    }
    
    /**
     * Get a parameter value from query string.
     *
     * Note: This method is not part of the PSR standard.
     *
     * @param string $key
     * @param mixed $default (optional)
     *
     * @return mixed
     */
    public function getQueryParam($key, $default = null)
    {
        $params = $this->getQueryParams();
        return ((isset($params[$key])) ? $params[$key] : $default);
    }
    
    /**
     * Get cookie value from cookies sent by the client to the server.
     *
     * Note: This method is not part of the PSR standard.
     *
     * @param string $key The attribute name.
     * @param mixed $default (optional) Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getCookieParam($key, $default = null)
    {
        $cookies = $this->getCookieParams();
        return ((isset($cookies[$key])) ? $cookies[$key] : $default);
    }
    
    /**
     * Check if any part of specified endpoints match the url endpoints.
     *
     * Note: This method is not part of the PSR standard.
     *
     * @param string|string[] $urlPaths Single/array of paths to match.
     * @param string $basePath (optional) Prepend path to specified url paths.
     * @param string $strict (optional) If true, specified endpoints must match exactly.
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function hasEndpoint($urlPaths, string $basePath = '', bool $strict = false): bool
    {
        if (! is_string($urlPaths) && ! is_array($urlPaths)) {
            throw new InvalidArgumentException('Endpoints can only be an array or a string');
        }
        
        $basePath = ($basePath === '') ? '' : trim($basePath, '/');
        $urlPaths = (array)$urlPaths;
        $reqUri = trim($this->getUri()->getPath(), '/');
        
        foreach ($urlPaths as $urlPath) {
            $urlPath = trim($urlPath, '/');
            $urlPath = (($urlPath === '' || $basePath === '') ? "{$basePath}{$urlPath}" : "$basePath/$urlPath");
            
            if (($strict || $urlPath === '') ? ($urlPath === $reqUri) : (preg_match('/\b' . preg_quote($urlPath, '/') . '\b/', $reqUri))) {
                return true;
            }
        }
        
        return false;
    }
    //strpos($reqUri, $urlPath) !== false
    /**
     * Check if the specified endpoint matches exactly to the one in the url.
     *
     * Note: This method is not part of the PSR standard.
     *
     * @param string|string[] $urlPath
     * @param string $basePath (optional)
     *
     * @return bool
     */
    public function isEndpoint($urlPath, string $basePath = ''): bool
    {
        return $this->hasEndpoint($urlPath, $basePath, true);
    }
    
    /**
     * Check if the request is an XHR request.
     *
     * Note: This method is not part of the PSR standard.
     *
     * @return bool
     */
    public function isXhr()
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }
}