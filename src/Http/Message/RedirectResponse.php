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

use Psr\Http\Message\UriInterface;
use BitFrame\Factory\HttpFactory;
use InvalidArgumentException;

use function is_string;
use function is_object;
use function sprintf;
use function get_class;
use function gettype;

/**
 * Http response for redirect.
 */
class RedirectResponse extends ResponseDecorator
{
    /**
     * @param string|UriInterface $redirectTo
     * @param integer $statusCode
     *
     * @return self
     */
    public static function create($redirectTo, int $statusCode = 302): self
    {
        return new self($redirectTo, $statusCode);
    }

    /**
     * @param string|UriInterface $redirectTo
     * @param int $statusCode
     *
     * @throws InvalidArgumentException
     */
    public function __construct($redirectTo, int $statusCode = 302)
    {
        if (! is_string($redirectTo) && ! $redirectTo instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expecting a string or %s instance; received "%s"',
                UriInterface::class,
                (is_object($redirectTo) ? get_class($redirectTo) : gettype($redirectTo))
            ));
        }

        $response = HttpFactory::createResponse()
            ->withStatus($statusCode)
            ->withHeader('Location', (string) $redirectTo);

        parent::__construct($response);
    }
}
