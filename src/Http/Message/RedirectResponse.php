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
use InvalidArgumentException;

/**
 * Http response for redirect.
 */
class RedirectResponse extends Response
{
    /**
     * @param string|UriInterface $redirectTo
     * @param integer $statusCode
     *
     * @return $this
     */
    public static function create($redirectTo, int $statusCode = 302): self
    {
        return new static($redirectTo, $statusCode);
    }

    /**
     * @param string|UriInterface $redirectTo
     * @param int $statusCode
     *
     * @throws InvalidArgumentException
     */
    public function __construct($redirectTo, int $statusCode = 302)
    {
        if (! \is_string($redirectTo) && ! $redirectTo instanceof UriInterface) {
            throw new InvalidArgumentException(\sprintf(
                'Expecting a string or %s instance; received "%s"',
                UriInterface::class,
                (\is_object($redirectTo) ? \get_class($redirectTo) : \gettype($redirectTo))
            ));
        }
        
        parent::__construct();

        $this->response = $this->response
            ->withStatus($statusCode)
            ->withHeader('Location', (string) $redirectTo);
    }
}
