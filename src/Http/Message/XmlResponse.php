<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

use BitFrame\Factory\HttpFactory;

/**
 * Http response containing XML data.
 */
class XmlResponse extends ResponseDecorator
{
    /** @var string */
    private const MIME_TYPE = 'application/xml';

    public static function create(string $xml): self
    {
        return new self($xml);
    }

    public function __construct(string $xml)
    {
        $factory = HttpFactory::getFactory();
        $response = $factory->createResponse()
            ->withHeader('Content-Type', self::MIME_TYPE . '; charset=utf-8')
            ->withBody($factory->createStream($xml));

        parent::__construct($response);
    }
}
