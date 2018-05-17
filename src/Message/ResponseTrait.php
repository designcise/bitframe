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
 * Provides extended, non-standard, functionality for 
 * the PSR-7 Http Response interface.
 */
trait ResponseTrait
{
    /**
     * Http Response with redirect.
     *
     * Note: This method is not part of the PSR standard, and is merely meant 
     * to be a shortcut for $response->withHeader('Location', ..)->withStatus(3xx);
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param string|UriInterface $url The redirect destination.
     * @param int|null $status (optional) The redirect HTTP status code.
     *
     * @return ResponseInterface
     *
     * @throws \InvalidArgumentException
     * @throws \BitFrame\Exception\HttpException
     */
    public function withRedirect($url, ?int $status = null): ResponseInterface
    {
        if (! is_string($url) && ! $url instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'Uri provided to %s MUST be a string or \Psr\Http\Message\UriInterface instance; received "%s"',
                __CLASS__,
                (is_object($url) ? get_class($url) : gettype($url))
            ));
        }
        
        $responseWithRedirect = $this->withHeader('Location', (string) $url);
        
        if ($status === null && $this->getStatusCode() === 200) {
            // @see: http://www.restapitutorial.com/httpstatuscodes.html
            $status = 302;
        } elseif ($status < 300 || $status >= 400) {
            // if http status code not 3xx
            throw new \BitFrame\Exception\HttpException(
                 "Invalid HTTP status code '$status' for redirect; must be 3xx"
            );
        }
        
        return (($status !== null) ? $responseWithRedirect->withStatus($status) : $responseWithRedirect);
    }
    
    /**
     * Http Response as JSON/JSONP.
     *
     * Note: This method is not part of the PSR standard, and is merely meant
     * to serve as a shortcut for preparing json data with correct headers along 
     * with whatever response body that may exist.
     *
     * This method prepares the response object to return an HTTP Json
     * response to the client.
     *
     * @param mixed $data The data.
     * @param int $status (optional) The HTTP status code.
     * @param string $bodyKeyName (optional) Name of index containing response body.
     * @param string $jsonpCallback (optional) Name of callback for JSONP calls.
     * @param int $encodingOptions (optional) Json encoding options.
     *
     * @return ResponseInterface
     *
     * @throws \UnexpectedValueException
     */
    public function withJson(
        $data, 
        ?int $status = null, 
        ?string $bodyKeyName = null,
        ?string $jsonpCallback = null,
        int $encodingOptions = 0
    ): ResponseInterface
    {
        // include body online if key name is not null
        if ($bodyKeyName !== null) {
            if ($bodyKeyName === '') {
                throw new \UnexpectedValueException('Response body key must not be empty');
            }

            // read current body data
            $body = $this->getBody();

            if ($body->isSeekable()) {
                $body->rewind();
            }

            $data[$bodyKeyName] = '';

            // no readable data in stream?
            if (! $body->isReadable()) {
                $data[$bodyKeyName] = $body;
            } else {
                // read data till end of stream is reached...
                while (! $body->eof()) {
                    // read 8mb (max buffer length) of binary data at a time and output it
                    $data[$bodyKeyName] .= $body->read(1024 * 8);
                }
            }
        }
        
        // ensure that the json encoding passed successfully
        if (($json = json_encode($data, $encodingOptions)) === false) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }
        
        // add padding if jsonp call
        if (! empty($jsonpCallback)) {
            $json = "{$jsonpCallback}({$json})";
        }
        
        // new data stream
        $response = $this->withBody(HttpMessageFactory::createStreamFromResource(fopen('php://temp', 'r+')));
        $response->getBody()->write($json);
        
        $responseWithJson = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
        
        return ((isset($status)) ? $responseWithJson->withStatus($status) : $responseWithJson);
    }
    
    /**
     * Http Response to force a file download.
     *
     * Note: This method is not part of the PSR standard, and is merely meant
     * to serve as a shortcut for forcing a file download with correct headers.
     *
     * This method prepares the response object to force the specified file to
     * be downloaded.
     *
     * @param string $file File name.
     * @param string $spoofedFileName File name the client sees.
     *
     * @return ResponseInterface
     *
     * @throws \BitFrame\Exception\FileNotReadableException
     */
    public function withDownload(string $file, string $spoofedFileName = ''): ResponseInterface
    {
        if (! is_readable($file)) {
            throw new \BitFrame\Exception\FileNotReadableException('File must be readable.');
        }
        
        $stream = HttpMessageFactory::createStreamFromFile($file, 'r');
        
        return $this
                    ->withHeader('Content-Type', (new \finfo(FILEINFO_MIME))->file($file) ?: 'application/octet-stream')
                    ->withHeader('Content-Disposition', 'attachment; filename=' . ($spoofedFileName ?: basename($file)))
                    ->withHeader('Content-Transfer-Encoding', 'Binary')
                    ->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Pragma', 'public')
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate')
                    ->withBody($stream)
                    ->withHeader('Content-Length', "{$stream->getSize()}");
    }
}