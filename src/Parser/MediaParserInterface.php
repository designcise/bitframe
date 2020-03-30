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

/**
 * Implementation for a media parser.
 */
interface MediaParserInterface
{
    /**
     * Parse `$input` string (for e.g., the http request body).
     *
     * @param string $input
     *
     * @return mixed
     */
    public function parse(string $input);
}
