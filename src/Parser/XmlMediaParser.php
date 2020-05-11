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

use function libxml_clear_errors;
use function libxml_disable_entity_loader;
use function libxml_use_internal_errors;
use function simplexml_load_string;

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
    public function parse(string $input)
    {
        $backup = libxml_disable_entity_loader(true);
        $backupErrors = libxml_use_internal_errors(true);
        $result = simplexml_load_string(
            $input,
            'SimpleXMLElement',
            self::OPTIONS
        );
        
        libxml_disable_entity_loader($backup);
        libxml_clear_errors();
        libxml_use_internal_errors($backupErrors);
        
        return $result ?: null;
    }
}
