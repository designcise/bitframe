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
 * Http response containing HTML data.
 */
class HtmlResponse extends Response
{
    /** @var string */
    private const MIME_TYPE = 'text/html';

    /**
     * @param string $html
     *
     * @return $this
     */
    public static function create(string $html): self
    {
        return new static($html);
    }

    /**
     * @param string $html
     */
    public function __construct(string $html)
    {
        parent::__construct();

        $this->response = $this->response
            ->withHeader('Content-Type', self::MIME_TYPE . '; charset=utf-8')
            ->withBody($this->factory->createStream($html));
    }
}
