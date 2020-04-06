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

use function json_encode;

use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

/**
 * Http response containing JSON data.
 */
class JsonResponse extends Response
{
    /** @var string */
    private const MIME_TYPE = 'application/json';

    /**
     * @param array $data
     * @param int $encodingOptions
     * @param int $maxDepth
     *
     * @return $this
     *
     * @throws \JsonException
     */
    public static function create(
        $data = [],
        int $encodingOptions = 0,
        int $maxDepth = 512
    ): self {
        return new static($data, $encodingOptions, $maxDepth);
    }

    /**
     * @param mixed $data Anything but a resource.
     * @param int $encodingOptions
     * @param int $maxDepth
     *
     * @throws \JsonException
     */
    public function __construct(
        $data = [],
        int $encodingOptions = 0,
        int $maxDepth = 512
    ) {
        parent::__construct();

        $encodingOptions |= JSON_THROW_ON_ERROR
            | JSON_HEX_QUOT
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_UNESCAPED_SLASHES;
        
        $json = json_encode($data, $encodingOptions, $maxDepth);

        $this->response = $this->response
            ->withHeader('Content-Type', self::MIME_TYPE . '; charset=utf-8')
            ->withBody($this->factory->createStream($json));
    }
}
