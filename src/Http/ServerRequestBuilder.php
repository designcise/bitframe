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
    StreamFactoryInterface,
    StreamInterface,
    UploadedFileInterface
};
use InvalidArgumentException;
use UnexpectedValueException;

use function array_keys;
use function is_array;
use function is_resource;
use function is_object;
use function ltrim;
use function parse_url;
use function preg_match_all;
use function rtrim;
use function strtolower;
use function substr;
use function str_replace;
use function urldecode;
use function strtr;
use function count;
use function sprintf;

use const PHP_URL_PORT;
use const PREG_SET_ORDER;

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

    /** @var null|array|object */
    private $parsedBody;

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
            $parser = (self::$preferredMediaParser)($request);
            $this->parsedBody = $parser->parse((string) $this->body);
        }

        if (
            null === $this->parsedBody
            || is_array($this->parsedBody)
            || is_object($this->parsedBody)
        ) {
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
        $this->method = (empty($this->server['REQUEST_METHOD']))
            ? 'GET'
            : $this->server['REQUEST_METHOD'];

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
            $query = $server['QUERY_STRING'];
        } elseif (! empty($uriParts['query'])) {
            $query = $uriParts['query'];
        }

        $fragment = (! empty($uriParts['fragment'])) ? $uriParts['fragment'] : '';

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
            $this->protocolVer = strtr((string) $this->server['SERVER_PROTOCOL'], ['HTTP/' => '']);

            $isNumeric = (int) $this->protocolVer;

            if (! $isNumeric) {
                throw new UnexpectedValueException(sprintf(
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
        preg_match_all($pattern, $str, $originalHeaders, PREG_SET_ORDER);

        $pattern = '/(redirect-)?(?:(?:http-)|(content-))([^ ]*)/';
        $str = strtolower(str_replace('_', '-', $str));
        preg_match_all($pattern, $str, $normalizedHeaders, PREG_SET_ORDER);

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
     * @param null|array|object $parsedBody
     *
     * @return $this
     */
    public function addParsedBody($parsedBody): self
    {
        if (null !== $parsedBody && ! is_array($parsedBody) && ! is_object($parsedBody)) {
            throw new InvalidArgumentException(
                'Parsed body can only be null, an array or an object'
            );
        }

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
        if (! $body instanceof StreamInterface) {
            if (is_resource($body)) {
                $body = $this->factory->createStreamFromResource($body);
            } elseif (! is_array($body) && ! is_object($body)) {
                $body = $this->factory->createStream((string) $body);
            }
        }

        if ($body instanceof StreamInterface && (string) $body !== '') {
            $this->body = $body;
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
            $this->uploadedFiles = self::normalizeUploadedFiles($files, $this->factory);
        }

        return $this;
    }

    /**
     * Create and return an UploadedFile instance from a `$_FILES` specification.
     *
     * If the specification represents an array of values, this method will loops
     * through all nested files and return a normalized array of `UploadedFileInterface`
     * instances.
     *
     * @param array $files `$_FILES` struct.
     * @param StreamFactoryInterface $streamFactory
     *
     * @return UploadedFileInterface[]|UploadedFileInterface
     */
    private static function createUploadedFileFromSpec(
        array $files,
        StreamFactoryInterface $streamFactory
    ) {
        if (is_array($files['tmp_name'])) {
            $normalizedFiles = [];

            foreach ($files['tmp_name'] as $key => $file) {
                $normalizedFiles[$key] = self::createUploadedFileFromSpec([
                    'tmp_name' => $files['tmp_name'][$key],
                    'size' => $files['size'][$key],
                    'error' => $files['error'][$key],
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                ], $streamFactory);
            }

            return $normalizedFiles;
        }

        $stream = ($files['tmp_name'] instanceof StreamInterface)
            ? $files['tmp_name']
            : $streamFactory->createStreamFromFile($files['tmp_name'], 'r+');

        return $streamFactory->createUploadedFile(
            $stream, $files['size'], (int) $files['error'], $files['name'], $files['type']
        );
    }

    /**
     * Transforms each value into an `UploadedFile` instance, and ensures that nested
     * arrays are normalized.
     *
     * @param array $files
     * @param StreamFactoryInterface $streamFactory
     *
     * @return UploadedFileInterface[]
     *
     * @throws InvalidArgumentException
     */
    private static function normalizeUploadedFiles(
        array $files,
        StreamFactoryInterface $streamFactory
    ): array {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value)) {
                $normalized[$key] = (isset($value['tmp_name']))
                    ? self::createUploadedFileFromSpec($value, $streamFactory)
                    : self::normalizeUploadedFiles($value, $streamFactory);
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
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
        preg_match_all('(
            (?:^\\n?[ \t]*|[;:][ ])
            (?P<name>[!#$%&\'*+-.0-9A-Z^_`a-z|~]+)
            =
            (?P<DQUOTE>"?)
                (?P<value>[\x21\x23-\x2b\x2d-\x3a\x3c-\x5b\x5d-\x7e]*)
            (?P=DQUOTE)
            (?=\\n?[ \t]*$|;[ ])
        )x', $cookieHeader, $matches, PREG_SET_ORDER);

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
        $totalMatches = count($normalizedHeaders);

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
}
