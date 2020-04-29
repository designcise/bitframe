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

use Psr\Http\Message\ServerRequestInterface;
use BitFrame\Parser\{
    MediaParserInterface,
    DefaultMediaParser,
    JsonMediaParser,
    XmlMediaParser
};
use InvalidArgumentException;

use function array_key_last;
use function asort;
use function is_a;
use function strpos;

/**
 * Determine media parser to use for an incoming Http request.
 */
class MediaParserNegotiator
{
    /** @var string */
    public const CONTENT_TYPE_DEFAULT = 'default';

    /** @var string */
    public const CONTENT_TYPE_JSON = 'json';

    /** @var string */
    public const CONTENT_TYPE_XML = 'xml';

    private static array $contentParsers = [
        self::CONTENT_TYPE_DEFAULT => DefaultMediaParser::class,
        self::CONTENT_TYPE_JSON => JsonMediaParser::class,
        self::CONTENT_TYPE_XML => XmlMediaParser::class,
    ];

    public static function add(string $type, string $parser): void
    {
        if (! is_a($parser, MediaParserInterface::class, true)) {
            throw new InvalidArgumentException('Parser must implement ' . MediaParserInterface::class);
        }

        self::$contentParsers[$type] = $parser;
    }

    public static function fromRequest(ServerRequestInterface $request): MediaParserInterface
    {
        $acceptTypes = $request->getHeader('accept');
        $default = self::$contentParsers[self::CONTENT_TYPE_DEFAULT];

        if (! isset($acceptTypes[0])) {
            return new $default();
        }

        $acceptType = $acceptTypes[0];
        $score = self::calculateRelevance($acceptType);

        asort($score);
        $parser = array_key_last($score);

        return ($score[$parser] === 0)
            ? new $default()
            : new $parser();
    }

    /**
     * @param string $acceptType
     * @return array
     */
    private static function calculateRelevance(string $acceptType): array
    {
        $score = [];
        foreach (self::$contentParsers as $format) {
            foreach ($format::MIMES as $value) {
                $score[$format] = $score[$format] ?? 0;
                $score[$format] += (int) (strpos($acceptType, $value) !== false);
            }
        }
        return $score;
    }
}
