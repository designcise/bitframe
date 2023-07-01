<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http;

use BitFrame\Http\Normalizer\UploadedFilesNormalizer;
use BitFrame\Http\Parser\MediaParserNegotiator;
use BitFrame\Http\Parser\{UriParser, HttpCookieParser};
use Psr\Http\Message\{
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface,
    ServerRequestInterface,
    StreamInterface,
};
use InvalidArgumentException;
use UnexpectedValueException;

use function array_keys;
use function is_array;
use function is_resource;
use function is_object;
use function strtr;
use function strtolower;
use function str_replace;
use function preg_match_all;
use function count;
use function sprintf;
use function implode;

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

    public function __construct(
        private readonly array $server,
        private readonly RequestFactoryInterface
        & ResponseFactoryInterface
        & ServerRequestFactoryInterface
        & StreamFactoryInterface
        & UploadedFileFactoryInterface
        & UriFactoryInterface $factory,
    ) {
        $this->request = $factory->createServerRequest('GET', '/', $server);
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
        [$uri, $queryParams] = UriParser::parse($this->server);

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
            && $this->server['SERVER_PROTOCOL'] !== "HTTP/$protocolVer"
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

        $pattern = '/(redirect-)?(?:http-|(content-))([^ ]*)/';
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
            $cookies = HttpCookieParser::parse($this->server['HTTP_COOKIE']);
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
        $uploadedFilesNormalizer = new UploadedFilesNormalizer($this->factory);
        $this->request = $this->request
            ->withUploadedFiles($uploadedFilesNormalizer->normalize($files));

        return $this;
    }
}
