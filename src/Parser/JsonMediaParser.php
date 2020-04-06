<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Parser;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;
use const JSON_BIGINT_AS_STRING;

/**
 * Parses string data into JSON.
 */
class JsonMediaParser implements MediaParserInterface
{
    /** @var int */
    protected const OPTIONS = JSON_THROW_ON_ERROR|JSON_BIGINT_AS_STRING;

    /**
     * {@inheritdoc}
     *
     * Note: Uses `JSON_THROW_ON_ERROR|JSON_BIGINT_AS_STRING` masks.
     *
     * @throws \JsonException
     */
    public function parse(string $input)
    {
        $result = json_decode($input, true, 512, self::OPTIONS);
        return ((is_array($result)) ? $result : null);
    }
}
