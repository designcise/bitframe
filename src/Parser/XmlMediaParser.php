<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Parser;

use SimpleXMLElement;

use const LIBXML_NOCDATA;

/**
 * Parses string data into XML.
 */
class XmlMediaParser implements MediaParserInterface
{
    /** @var string[] */
    public const MIMES = ['text/xml', 'application/xml', 'application/x-xml'];

    /** @var int */
    private const OPTIONS = LIBXML_NOCDATA;

    /**
     * {@inheritdoc}
     */
    public function parse(string $input): ?SimpleXMLElement
    {
        return new SimpleXMLElement($input, self::OPTIONS) ?: null;
    }
}
