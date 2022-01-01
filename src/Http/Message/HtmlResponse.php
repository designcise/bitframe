<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

use BitFrame\Factory\HttpFactory;

/**
 * Http response containing HTML data.
 */
class HtmlResponse extends ResponseDecorator
{
    /** @var string */
    private const MIME_TYPE = 'text/html';

    public static function create(string $html): self
    {
        return new self($html);
    }

    public function __construct(string $html)
    {
        $factory = HttpFactory::getFactory();
        $response = $factory->createResponse()
            ->withHeader('Content-Type', self::MIME_TYPE . '; charset=utf-8')
            ->withBody($factory->createStream($html));

        parent::__construct($response);
    }
}
