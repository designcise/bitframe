<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

use BitFrame\Factory\HttpFactory;
use InvalidArgumentException;

use function explode;
use function in_array;
use function json_encode;
use function preg_match;

use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

/**
 * Http response containing JSON data with padding.
 */
class JsonpResponse extends ResponseDecorator
{
    /** @var string */
    private const MIME_TYPE = 'application/javascript';

    /**
     * @param mixed $data
     * @param string $callback
     * @param int $encodingOptions
     * @param int $maxDepth
     *
     * @return self
     *
     * @throws \JsonException
     */
    public static function create(
        mixed $data,
        string $callback,
        int $encodingOptions = 0,
        int $maxDepth = 512,
    ): self {
        return new self($data, $callback, $encodingOptions, $maxDepth);
    }

    /**
     * @param mixed $data Anything but a resource.
     * @param string $callback
     * @param int $encodingOptions
     * @param int $maxDepth
     *
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function __construct(
        mixed $data,
        string $callback,
        int $encodingOptions = 0,
        int $maxDepth = 512,
    ) {
        if (empty($callback)) {
            throw new InvalidArgumentException('Callback cannot be empty');
        }

        if (! $this->isCallbackValid($callback)) {
            throw new InvalidArgumentException('Callback name is invalid');
        }

        $encodingOptions |= JSON_THROW_ON_ERROR
            | JSON_HEX_QUOT
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_UNESCAPED_SLASHES;

        $body = $callback . '(' . json_encode($data, $encodingOptions, $maxDepth) . ')';

        $factory = HttpFactory::getFactory();
        $response = $factory->createResponse()
            ->withHeader('Content-Type', self::MIME_TYPE . '; charset=utf-8')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withBody($factory->createStream($body));

        parent::__construct($response);
    }

    /**
     * @param string $callback
     *
     * @return bool
     *
     * @see \Symfony\Component\HttpFoundation\JsonResponse::setCallback()
     */
    private function isCallbackValid(string $callback): bool
    {
        $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';

        $reserved = [
            'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally',
            'return','void', 'continue', 'for', 'switch', 'while', 'debugger', 'function', 'this',
            'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super',
            'const', 'export', 'import', 'implements', 'let', 'private', 'public', 'yield', 'interface',
            'package', 'protected', 'static', 'null', 'true', 'false',
        ];

        $parts = explode('.', $callback);

        foreach ($parts as $part) {
            if (! preg_match($pattern, $part) || in_array($part, $reserved, true)) {
                return false;
            }
        }

        return true;
    }
}
