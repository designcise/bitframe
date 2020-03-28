<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

/**
 * Http response containing XML data.
 */
class XmlResponse extends Response
{
    /** @var string */
    private const MIME_TYPE = 'application/xml';

    /**
     * @param string $xml
     *
     * @return $this
     */
    public static function create(string $xml): self
    {
        return new static($xml);
    }

    /**
     * @param string $xml
     */
    public function __construct(string $xml)
    {
        parent::__construct();

        $this->response = $this->response
            ->withHeader('Content-Type', self::MIME_TYPE . '; charset=utf-8')
            ->withBody($this->factory->createStream($xml));
    }
}