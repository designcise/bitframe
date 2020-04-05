<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http;

use Psr\Http\Message\{
    ServerRequestInterface,
    StreamInterface,
    UploadedFileInterface
};
use InvalidArgumentException;
use UnexpectedValueException;

use function array_keys;
use function is_array;
use function is_resource;
use function is_string;
use function ltrim;
use function parse_url;
use function preg_match_all;
use function rtrim;
use function strtolower;
use function substr;

use const PHP_URL_PORT;

/**
 * Builds a server request.
 */
class ServerRequestBuilder
{
    /** @var callable */
    private static $preferredMediaParser = [ContentNegotiator::class, 'getPreferredMediaParserFromRequest'];

    /** @var array */
    private array $server;

    /** @var callable */
    private $factory;

    /** @var string */
    private string $method = 'GET';

    /** @var string */
    private string $uri = '/';

    /** @var string */
    private string $protocolVer = '1.1';

    /** @var array */
    private array $headers = [];

    /** @var array */
    private array $cookieParams = [];

    /** @var null|array */
    private ?array $parsedBody;

    /** @var null|StreamInterface */
    private ?StreamInterface $body = null;

    /** @var UploadedFileInterface[] */
    private array $uploadedFiles = [];

    /**
     * @param array $server
     * @param object $factory
     * @param null|array $parsedBody
     * @param array $cookies
     * @param array $files
     * @param string $body
     *
     * @return ServerRequestInterface
     */
    public static function fromSapi(
        array $server,
        object $factory,
        ?array $parsedBody = null,
        array $cookies = [],
        array $files = [],
        $body = ''
    ): ServerRequestInterface {
        $builder = new static($server, $factory);

        return $builder
            ->addMethod()
            ->addUri()
            ->addProtocolVersion()
            ->addHeaders()
            ->addCookieParams($cookies)
            ->addUploadedFiles($files)
            ->addParsedBody($parsedBody)
            ->addBody($body)
            ->build();
    }

    /**
     * @param array $server
     * @param object $factory
     */
    public function __construct(array $server, object $factory)
    {
        $this->server = $server;
        $this->factory = $factory;
    }

    /**
     * @return ServerRequestInterface
     */
    public function build(): ServerRequestInterface
    {
        $request = $this->factory->createServerRequest($this->method, $this->uri, $this->server)
            ->withProtocolVersion($this->protocolVer);

        if (! empty($this->headers)) {
            $request = $this->addHeadersToServerRequest($request);
        }

        if (! empty($this->cookieParams)) {
            $request = $request->withCookieParams($this->cookieParams);
        }

        $isBodyEmpty = (null === $this->body);

        if (empty($this->parsedBody) && ! $isBodyEmpty) {
            $parser = \call_user_func(self::$preferredMediaParser, $request);
            $this->parsedBody = $parser->parse((string) $this->body);
        }

        if (! empty($this->parsedBody)) {
            $request = $request->withParsedBody($this->parsedBody);
        }

        if (! $isBodyEmpty) {
            $request = $request->withBody($this->body);
        }

        if (! empty($this->uploadedFiles)) {
            $request = $request->withUploadedFiles($this->uploadedFiles);
        }

        return $request;
    }

    /**
     * @return $this
     */
    public function addMethod(): self
    {
        $this->method = $this->server['REQUEST_METHOD'] ?: 'GET';
        return $this;
    }

    /**
     * @return $this
     */
    public function addUri(): self
    {
        $server = $this->server;

        $uriParts = isset($server['REQUEST_URI']) ? parse_url($server['REQUEST_URI']) : [];
        $path = '';

        if (! empty($server['PATH_INFO'])) {
            $path = $server['PATH_INFO'];
        } elseif (! empty($server['ORIG_PATH_INFO'])) {
            $path = $server['ORIG_PATH_INFO'];
        } elseif (! empty($uriParts['path'])) {
            $path = $uriParts['path'];
        }

        $query = '';

        if (! empty($server['QUERY_STRING'])) {
            $path = $server['QUERY_STRING'];
        } elseif (! empty($uriParts['query'])) {
            $path = $uriParts['query'];
        }

        $fragment = (empty($uriParts['fragment'])) ? $uriParts['fragment'] : '';

        $baseUri = $this->getUriAuthorityWithScheme();

        if ($path) {
            $baseUri = rtrim($baseUri, '/') . '/' . ltrim($path, '/');
        }

        $this->uri = (
            ($baseUri ?: '/')
            . ($query ? ('?' . ltrim($query, '?')) : '')
            . ($fragment ? "#{$fragment}" : '')
        );

        return $this;
    }

    /**
     * @return $this
     *
     * @throws UnexpectedValueException
     */
    public function addProtocolVersion(): self
    {
        if (
            isset($this->server['SERVER_PROTOCOL'])
            && $this->server['SERVER_PROTOCOL'] !== "HTTP/{$this->protocolVer}"
        ) {
            $this->protocolVer = \strtr($this->server['SERVER_PROTOCOL'], ['HTTP/' => '']);

            $isNumeric = (int) $this->protocolVer;

            if (! $isNumeric) {
                throw new UnexpectedValueException(\sprintf(
                    'Unrecognized protocol version "%s"',
                    $this->protocolVer
                ));
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addHeaders(): self
    {
        $pattern = '/(REDIRECT_)?(HTTP_|CONTENT_)([^ ]*)/i';
        $str = implode(' ', array_keys($this->server));
        preg_match_all($pattern, $str, $originalHeaders, \PREG_SET_ORDER);

        $pattern = '/(redirect-)?(?:(?:http-)|(content-))([^ ]*)/';
        $str = strtolower(\str_replace('_', '-', $str));
        preg_match_all($pattern, $str, $normalizedHeaders, \PREG_SET_ORDER);

        $this->headers = [
            'original' => $originalHeaders,
            'normalized' => $normalizedHeaders,
        ];

        return $this;
    }

    /**
     * @param array $cookies
     *
     * @return $this
     */
    public function addCookieParams(array $cookies): self
    {
        if ($cookies === [] && isset($this->server['HTTP_COOKIE'])) {
            $cookies = $this->parseCookieHeader($this->server['HTTP_COOKIE']);
        }

        if (! empty($cookies)) {
            $this->cookieParams = $cookies;
        }

        return $this;
    }

    /**
     * @param null|array $parsedBody
     *
     * @return $this
     */
    public function addParsedBody(?array $parsedBody): self
    {
        $this->parsedBody = $parsedBody;
        return $this;
    }

    /**
     * @param string|resource|StreamInterface $body
     *
     * @return $this
     */
    public function addBody($body): self
    {
        if (! empty($body)) {
            $body = $this->getBodyAsStream($body);

            if ((string) $body !== '') {
                $this->body = $body;
            }
        }

        return $this;
    }

    /**
     * Transforms each value into an `UploadedFile` instance, and ensures that nested
     * arrays are normalized.
     *
     * @param array $files
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function addUploadedFiles(array $files): self
    {
        if (! empty($files)) {
            foreach ($files as $key => $value) {
                if ($value instanceof UploadedFileInterface) {
                    $this->uploadedFiles[$key] = $value;
                } elseif (is_array($value)) {
                    $this->uploadedFiles[$key] = (isset($value['tmp_name']))
                        ? $this->createUploadedFileFromSpec($value)
                        : $this->addUploadedFiles($value);

                    continue;
                }

                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getUriAuthorityWithScheme(): string
    {
        $server = $this->server;

        $authority = $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? $server['SERVER_ADDR'] ?? '';

        if ($authority) {
            $scheme = (
                    $server['REQUEST_SCHEME']
                    ?? ('http' . ((isset($server['HTTPS']) && $server['HTTPS'] === 'on') ? 's' : ''))
                ) . ':';

            $authority = "{$scheme}//{$authority}";
        }

        if (
            ! $authority
            || ! isset($server['SERVER_PORT'])
            || parse_url($authority, PHP_URL_PORT)
        ) {
            return $authority;
        }

        return (
        (substr($authority, -1) === '/')
            ? rtrim($authority, '/') . ":{$server['SERVER_PORT']}/"
            : "{$authority}:{$server['SERVER_PORT']}"
        );
    }

    /**
     * Create and return an UploadedFile instance from a `$_FILES` specification.
     *
     * If the specification represents an array of values, this method will loops
     * through all nested files and return a normalized array of `UploadedFileInterface`
     * instances.
     *
     * @param array $files `$_FILES` struct.
     * @return UploadedFileInterface[]|UploadedFileInterface
     */
    private function createUploadedFileFromSpec(array $files)
    {
        $streamFactory = $this->factory;

        if (is_array($files['tmp_name'])) {
            $normalizedFiles = [];

            foreach ($files['tmp_name'] as $key => $file) {
                $normalizedFiles[$key] = $this->createUploadedFileFromSpec([
                    'tmp_name' => $files['tmp_name'][$key],
                    'size' => $files['size'][$key],
                    'error' => $files['error'][$key],
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                ]);
            }

            return $normalizedFiles;
        }

        $stream = ($files['tmp_name'] instanceof StreamInterface)
            ? $files['tmp_name']
            : $streamFactory->createStreamFromFile($files['tmp_name'], 'r+');

        return $streamFactory->createUploadedFile(
            $stream,
            $files['size'],
            (int) $files['error'],
            $files['name'],
            $files['type']
        );
    }

    /**
     * Parse a cookie header according to RFC-6265.
     *
     * PHP will replace special characters in cookie names, which
     * results in other cookies not being available due to
     * overwriting. Thus, the server request should take the cookies
     * from the request header instead.
     *
     * @param string $cookieHeader
     *
     * @return array key/value cookie pairs.
     */
    private function parseCookieHeader(string $cookieHeader): array
    {
        preg_match_all(
            '/^(?:Set-Cookie:)\s*(?P<name>[^=]*)=(?P<value>[^;]*)/i',
            $cookieHeader,
            $matches,
            \PREG_SET_ORDER
        );
        $cookies = [];

        foreach ($matches as $match) {
            $cookies[$match['name']] = \urldecode($match['value']);
        }

        return $cookies;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ServerRequestInterface
     */
    private function addHeadersToServerRequest(
        ServerRequestInterface $request
    ): ServerRequestInterface {
        $originalHeaders = $this->headers['original'];
        $normalizedHeaders = $this->headers['normalized'];
        $totalMatches = \count($normalizedHeaders);

        for ($i = 0; $i < $totalMatches; $i++) {
            $isRedirect = ! (empty($normalizedHeaders[$i][1]));
            $originalKey = $originalHeaders[$i][2] . $originalHeaders[$i][3];

            // apache prefixes environment variables with `REDIRECT_` if they are
            // added by rewrite rules
            if ($isRedirect) {
                if (isset($this->server[$originalKey])) {
                    continue;
                }

                $originalKey = 'REDIRECT_' . $originalKey;
            }

            $newKey = $normalizedHeaders[$i][2] . $normalizedHeaders[$i][3];

            $request = $request->withHeader($newKey, $this->server[$originalKey]);
        }

        return $request;
    }

    /**
     * @param string|resource|StreamInterface $body
     *
     * @return StreamInterface
     */
    private function getBodyAsStream($body): StreamInterface
    {
        if (! $body instanceof StreamInterface) {
            if (is_string($body)) {
                $body = $this->factory->createStream($body);
            } elseif (is_resource($body)) {
                $body = $this->factory->createStreamFromResource($body);
            }
        }

        return $body;
    }
}
