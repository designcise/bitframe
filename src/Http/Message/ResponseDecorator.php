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

use Psr\Http\Message\{ResponseInterface, StreamInterface};

/**
 * Http Response.
 */
class ResponseDecorator implements ResponseInterface
{
    /** @var ResponseInterface */
    private ResponseInterface $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $new = clone $this;
        $new->setResponse($this->response->withStatus($code, $reasonPhrase));
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->setResponse($this->response->withHeader($name, $value));
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $new = clone $this;
        $new->setResponse($this->response->withAddedHeader($name, $value));
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $new = clone $this;
        $new->setResponse($this->response->withoutHeader($name));
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->setResponse($this->response->withProtocolVersion($version));
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->setResponse($this->response->withBody($body));
        return $new;
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

    /**
     * @param ResponseInterface $response
     *
     * @return $this
     */
    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;
        return $this;
    }
}
