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

use function rtrim;
use function parse_url;

use const PHP_URL_PORT;

/**
 * Builds a server request.
 */
class UriParser
{
    public static function parse(array $server): array
    {
        $uriParts = parse_url($server['REQUEST_URI'] ?? '');
        [$path, $query, $fragment] = self::extractComponents($server, $uriParts);

        $baseUri = self::createAuthorityWithScheme($server);

        if ($path) {
            $baseUri = rtrim($baseUri, '/') . '/' . ltrim($path, '/');
        }

        $uri = (
            ($baseUri ?: '/')
            . ($query ? ('?' . ltrim($query, '?')) : '')
            . ($fragment ? "#$fragment" : '')
        );

        parse_str($query, $queryParams);

        return [$uri, $queryParams];
    }

    private static function createAuthorityWithScheme(array $server): string
    {
        $authority = $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? $server['SERVER_ADDR'] ?? '';

        if ($authority) {
            $scheme = (
                    $server['REQUEST_SCHEME']
                    ?? ('http' . ((isset($server['HTTPS']) && $server['HTTPS'] === 'on') ? 's' : ''))
                ) . ':';

            $authority = "$scheme//$authority";
        }

        if (
            ! $authority
            || ! isset($server['SERVER_PORT'])
            || parse_url($authority, PHP_URL_PORT)
        ) {
            return $authority;
        }

        return (
        (str_ends_with($authority, '/'))
            ? rtrim($authority, '/') . ":{$server['SERVER_PORT']}/"
            : "$authority:{$server['SERVER_PORT']}"
        );
    }

    private static function extractComponents(array $server, array $uriParts): array
    {
        $path = '';

        if (! empty($server['PATH_INFO'])) {
            $path = $server['PATH_INFO'];
        } elseif (! empty($server['ORIG_PATH_INFO'])) {
            $path = $server['ORIG_PATH_INFO'];
        } elseif (! empty($uriParts['path'])) {
            $path = $uriParts['path'];
        }

        $query = '';

        if (! empty($server['QUERY_STRING'])) {
            $query = $server['QUERY_STRING'];
        } elseif (! empty($uriParts['query'])) {
            $query = $uriParts['query'];
        }

        $fragment = (! empty($uriParts['fragment'])) ? $uriParts['fragment'] : '';
        return [$path, $query, $fragment];
    }
}
