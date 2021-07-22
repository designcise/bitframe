<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http;

use BitFrame\Factory\{PSR17FactoryInterface, HttpFactory};
use BitFrame\Parser\MediaParserNegotiator;
use Psr\Http\Message\{
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    ServerRequestInterface,
    StreamInterface,
    UploadedFileInterface,
    UriFactoryInterface
};
use InvalidArgumentException;
use UnexpectedValueException;

use function array_keys;
use function is_array;
use function is_resource;
use function is_object;
use function ltrim;
use function rtrim;
use function strtr;
use function substr;
use function parse_str;
use function parse_url;
use function strtolower;
use function str_replace;
use function preg_match_all;
use function count;
use function sprintf;
use function implode;
use function urldecode;

use const PHP_URL_PORT;
use const PREG_SET_ORDER;

/**
 * Builds a server request.
 */
class ServerRequestBuilder
{
    /** @var callable */
    private static $preferredMediaParser = MediaParserNegotiator::class;

    private ServerRequestInterface $request;

    private ?StreamInterface $body = null;

    private object|null|array $parsedBody;

    public static function fromSapi(
        array $server,
        object $factory,
        ?array $parsedBody = null,
        array $cookies = [],
        array $files = [],
        $body = '',
    ): ServerRequestInterface {
        $builder = new self($server, $factory);

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

    public function __construct(
        private array $server,
        private object $factory,
    ) {
        $this->request = (HttpFactory::isPsr17Factory($factory))
            ? $factory->createServerRequest('GET', '/', $server)
            : throw new InvalidArgumentException('Http factory must implement all PSR-17 factories');
    }

    public function build(): ServerRequestInterface
    {
        if (empty($this->parsedBody) && ! empty((string) $this->body)) {
            $parser = new self::$preferredMediaParser($this->request);

            $this->request = $this->request
                ->withParsedBody($parser->parse((string) $this->body));
        }

        return $this->request;
    }

    public function addMethod(): self
    {
        if (! empty($this->server['REQUEST_METHOD'])) {
            $this->request = $this->request->withMethod($this->server['REQUEST_METHOD']);
        }

        return $this;
    }

    public function addUri(): self
    {
        $uriParts = parse_url($this->server['REQUEST_URI'] ?? '');
        [$path, $query, $fragment] = $this->extractUriComponents($this->server, $uriParts);

        $baseUri = $this->getUriAuthorityWithScheme();

        if ($path) {
            $baseUri = rtrim($baseUri, '/') . '/' . ltrim($path, '/');
        }

        $uri = (
            ($baseUri ?: '/')
            . ($query ? ('?' . ltrim($query, '?')) : '')
            . ($fragment ? "#{$fragment}" : '')
        );

        parse_str($query, $queryParams);

        $this->request = $this->request
            ->withUri($this->factory->createUri($uri))
            ->withQueryParams($queryParams);

        return $this;
    }

    /**
     * @return $this
     *
     * @throws UnexpectedValueException
     */
    public function addProtocolVersion(): self
    {
        $protocolVer = '1.1';

        if (
            isset($this->server['SERVER_PROTOCOL'])
            && $this->server['SERVER_PROTOCOL'] !== "HTTP/{$protocolVer}"
        ) {
            $protocolVer = strtr((string) $this->server['SERVER_PROTOCOL'], ['HTTP/' => '']);

            $isNumeric = (int) $protocolVer;

            if (! $isNumeric) {
                throw new UnexpectedValueException(sprintf('Unrecognized protocol version "%s"', $protocolVer));
            }
        }

        $this->request = $this->request->withProtocolVersion($protocolVer);

        return $this;
    }

    public function addHeaders(): self
    {
        $pattern = '/(REDIRECT_)?(HTTP_|CONTENT_)([^ ]*)/i';
        $str = implode(' ', array_keys($this->server));
        preg_match_all($pattern, $str, $originalHeaders, PREG_SET_ORDER);

        $pattern = '/(redirect-)?(?:(?:http-)|(content-))([^ ]*)/';
        $str = strtolower(str_replace('_', '-', $str));
        preg_match_all($pattern, $str, $normalizedHeaders, PREG_SET_ORDER);

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

            $this->request = $this->request
                ->withHeader($newKey, $this->server[$originalKey]);
        }

        return $this;
    }

    public function addCookieParams(array $cookies): self
    {
        if ($cookies === [] && isset($this->server['HTTP_COOKIE'])) {
            $cookies = $this->parseCookieHeader($this->server['HTTP_COOKIE']);
        }

        $this->request = $this->request
            ->withCookieParams($cookies ?: []);

        return $this;
    }

    public function addParsedBody(object|array|null $parsedBody): self
    {
        $this->request = $this->request
            ->withParsedBody($parsedBody);

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
            $this->request = $this->request->withBody($body);
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
        $this->request = $this->request
            ->withUploadedFiles($this->normalizeUploadedFiles($files));

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
     *
     * @return UploadedFileInterface[]|UploadedFileInterface
     */
    private function createUploadedFileFromSpec(array $files)
    {
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
            : $this->factory->createStreamFromFile($files['tmp_name'], 'r+');

        return $this->factory->createUploadedFile(
            $stream,
            $files['size'],
            (int) $files['error'],
            $files['name'],
            $files['type']
        );
    }

    /**
     * Transforms each value into an `UploadedFile` instance, and ensures that nested
     * arrays are normalized.
     *
     * @param array $files
     *
     * @return UploadedFileInterface[]
     *
     * @throws InvalidArgumentException
     */
    private function normalizeUploadedFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = (isset($value['tmp_name']))
                    ? $this->createUploadedFileFromSpec($value)
                    : $this->normalizeUploadedFiles($value);
                continue;
            }

            throw new InvalidArgumentException('Invalid value in files specification');
        }

        return $normalized;
    }

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
            $cookies[$match['name']] = urldecode($match['value']);
        }

        return $cookies;
    }

    private function extractUriComponents(array $server, array $uriParts): array
    {
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
        return [$path, $query, $fragment];
    }
}
