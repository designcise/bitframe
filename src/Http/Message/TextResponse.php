<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

use BitFrame\Factory\HttpFactory;

/**
 * Http response containing plain text.
 */
class TextResponse extends ResponseDecorator
{
    /** @var string */
    private const MIME_TYPE = 'text/plain';

    /**
     * @param string $text
     *
     * @return $this
     */
    public static function create(string $text): self
    {
        return new static($text);
    }

    /**
     * @param string $text
     */
    public function __construct(string $text)
    {
        $factory = HttpFactory::getFactory();
        $response = $factory->createResponse()
            ->withHeader('Content-Type', self::MIME_TYPE . '; charset=utf-8')
            ->withBody($factory->createStream($text));

        parent::__construct($response);
    }
}