<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

use Psr\Http\Message\UriInterface;
use BitFrame\Factory\HttpFactory;
use InvalidArgumentException;

/**
 * Http response for redirect.
 */
class RedirectResponse extends ResponseDecorator
{
    /**
     * @param string|UriInterface $redirectTo
     * @param int $statusCode
     *
     * @return self
     */
    public static function create(string|UriInterface $redirectTo, int $statusCode = 302): self
    {
        return new self($redirectTo, $statusCode);
    }

    /**
     * @param string|UriInterface $redirectTo
     * @param int $statusCode
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string|UriInterface $redirectTo, int $statusCode = 302)
    {
        $response = HttpFactory::createResponse()
            ->withStatus($statusCode)
            ->withHeader('Location', (string) $redirectTo);

        parent::__construct($response);
    }
}
