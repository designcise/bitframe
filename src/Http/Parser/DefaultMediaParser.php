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

use function parse_str;

/**
 * Parses string data into a variable.
 */
class DefaultMediaParser implements MediaParserInterface
{
    /** @var string[] */
    public const MIMES = ['text/html', 'application/xhtml+xml', 'text/plain'];

    /**
     * {@inheritdoc}
     */
    public function parse(string $input): mixed
    {
        parse_str($input, $data);
        return $data;
    }
}
