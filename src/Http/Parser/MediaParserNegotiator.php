<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Parser;

use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;

use function array_key_last;
use function asort;
use function is_a;

/**
 * Determine media parser to use for an incoming Http request.
 */
class MediaParserNegotiator implements MediaParserInterface
{
    /** @var string */
    final public const CONTENT_TYPE_DEFAULT = 'default';

    /** @var string */
    final public const CONTENT_TYPE_JSON = 'json';

    /** @var string */
    final public const CONTENT_TYPE_XML = 'xml';

    private array $contentParsers = [
        self::CONTENT_TYPE_DEFAULT => DefaultMediaParser::class,
        self::CONTENT_TYPE_JSON => JsonMediaParser::class,
        self::CONTENT_TYPE_XML => XmlMediaParser::class,
    ];

    private ?MediaParserInterface $activeParser = null;

    public function __construct(private readonly ServerRequestInterface $request)
    {
    }

    public function add(string $type, string $parser): void
    {
        if (! is_a($parser, MediaParserInterface::class, true)) {
            throw new InvalidArgumentException('Parser must implement ' . MediaParserInterface::class);
        }

        $this->contentParsers[$type] = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $input): mixed
    {
        return $this->createPreferredMediaParser()->parse($input);
    }

    public function createPreferredMediaParser(): MediaParserInterface
    {
        if ($this->activeParser instanceof MediaParserInterface) {
            return $this->activeParser;
        }

        $acceptTypes = $this->request->getHeader('accept');
        $default = $this->contentParsers[self::CONTENT_TYPE_DEFAULT];

        if (! isset($acceptTypes[0])) {
            $this->activeParser = new $default();
            return $this->activeParser;
        }

        $acceptType = $acceptTypes[0];
        $score = $this->calculateRelevance($acceptType);

        asort($score);
        $parser = array_key_last($score);

        $this->activeParser = ($score[$parser] === 0) ? new $default() : new $parser();

        return $this->activeParser;
    }

    private function calculateRelevance(string $acceptType): array
    {
        $score = [];
        foreach ($this->contentParsers as $parser) {
            foreach ($parser::MIMES as $value) {
                $score[$parser] = $score[$parser] ?? 0;
                $score[$parser] += (int) (str_contains($acceptType, $value));
            }
        }
        return $score;
    }
}
