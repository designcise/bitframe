<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2019 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Message;

use Psr\Http\Message\{ResponseInterface, StreamInterface};
use BitFrame\Factory\HttpFactory;

/**
 * Http Response.
 */
class Response implements ResponseInterface
{
    /** @var ResponseInterface */
    protected ResponseInterface $response;

    /** @var object */
    protected object $factory;

    public function __construct()
    {
        $this->factory = HttpFactory::getFactory();
        $this->response = $this->factory->createResponse();
    }
    
    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->response = $this->response->withStatus($code, $reasonPhrase);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $this->response = $this->response->withHeader($name, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $this->response = $this->response->withAddedHeader($name, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $this->response = $this->response->withoutHeader($name);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $this->response = $this->response->withProtocolVersion($version);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $this->response = $this->response->withBody($body);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->response->getBody();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }
}
