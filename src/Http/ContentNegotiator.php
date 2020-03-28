<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http;

use Psr\Http\Message\ServerRequestInterface;
use BitFrame\Parser\MediaParserInterface;

use function array_key_last;
use function asort;
use function is_a;
use function is_string;
use function strpos;

/**
 * Determine media parser to use for an incoming Http request.
 */
class ContentNegotiator
{
    /** @var string */
    public const CONTENT_TYPE_DEFAULT = 'default';

    /** @var string */
    public const CONTENT_TYPE_TEXT = 'text';

    /** @var string */
    public const CONTENT_TYPE_HTML = 'html';

    /** @var string */
    public const CONTENT_TYPE_JSON = 'json';

    /** @var string */
    public const CONTENT_TYPE_XML = 'xml';

    /** @var array Supported MIME types per content type. */
    private static $contentTypes = [
        self::CONTENT_TYPE_HTML => ['text/html', 'application/xhtml+xml'],
        self::CONTENT_TYPE_JSON => ['application/json', 'text/json', 'application/x-json'],
        self::CONTENT_TYPE_XML => ['text/xml', 'application/xml', 'application/x-xml'],
        self::CONTENT_TYPE_TEXT => ['text/plain']
    ];

    /** @var array */
    private static $contentParsers = [
        self::CONTENT_TYPE_DEFAULT => 'BitFrame\Parser\DefaultMediaParser',
        self::CONTENT_TYPE_JSON => 'BitFrame\Parser\JsonMediaParser',
        self::CONTENT_TYPE_XML => 'BitFrame\Parser\XmlMediaParser',
    ];

    /**
     * @param string $type
     * @param string $mime
     */
    public static function addContentType(string $type, string $mime)
    {
        self::$contentTypes[$type][] = $mime;
    }

    /**
     * Add/update content/media parsers.
     *
     * @param string $type
     * @param string|MediaParserInterface $parser
     */
    public static function addMediaParser(string $type, $parser)
    {
        if ($parser instanceof MediaParserInterface) {
            self::$contentParsers[$type] = $parser;
        } elseif (
            is_string($parser)
            && is_a($parser, MediaParserInterface::class, true)
        ) {
            self::$contentParsers[$type] = new $parser();
        }
    }

    /**
     * Get preferred media parser from `$request`.
     *
     * @param ServerRequestInterface $request
     *
     * @return MediaParserInterface
     */
    public static function getPreferredMediaParserFromRequest(
        ServerRequestInterface $request
    ): MediaParserInterface {
        $contentType = self::getPreferredContentTypeFromRequest($request);
        return self::getMediaParserForContentType($contentType);
    }

    /**
     * Get media parser based on `$contentType`.
     *
     * @param string $contentType
     *
     * @return MediaParserInterface
     */
    public static function getMediaParserForContentType(string $contentType): MediaParserInterface
    {
        if (! isset(self::$contentParsers[$contentType])) {
            $contentType = self::CONTENT_TYPE_DEFAULT;
        }

        $parser = self::$contentParsers[$contentType];

        if (
            is_string($parser)
            && is_a($parser, MediaParserInterface::class, true)
        ) {
            $parser = self::$contentParsers[$contentType] = new self::$contentParsers[$contentType]();
        }

        return $parser;
    }

    /**
     * Returns the preferred format based on the `Accept` header.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public static function getPreferredContentTypeFromRequest(
        ServerRequestInterface $request
    ): string {
        $acceptTypes = $request->getHeader('accept');

        if (isset($acceptTypes[0])) {
            $acceptType = $acceptTypes[0];

            // as many formats may match for a given `Accept` header, look for the one
            // that has the best relevance score
            $score = [];
            foreach (self::$contentTypes as $format => $values) {
                foreach ($values as $value) {
                    $score[$format] = $score[$format] ?? 0;
                    $score[$format] += (int) (strpos($acceptType, $value) !== false);
                }
            }

            // sort the array to retrieve the format that best matches the `Accept` header
            asort($score);

            return array_key_last($score);
        }

        return self::CONTENT_TYPE_HTML;
    }
}
