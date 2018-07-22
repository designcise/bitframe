<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Factory;

use \Interop\Http\Factory\ResponseFactoryInterface;
use \Psr\Http\Message\ResponseInterface;

use BitFrame\Message\ResponseTrait;

/**
 * Create the default http response object.
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if (class_exists('Zend\\Diactoros\\Response')) {
            return new class($code, $reasonPhrase) extends \Zend\Diactoros\Response {
                use ResponseTrait;
                
                /**
                 * @var string
                 */
                private $reasonPhrase;
                
                public function __construct($code, $reasonPhrase) {
                    parent::__construct('php://memory', $code);
                    
                    $this->reasonPhrase = $reasonPhrase;
                }

                /**
                 * {@inheritdoc}
                 */
                public function getReasonPhrase()
                {
                    if (! $this->reasonPhrase
                        && isset($this->phrases[$this->statusCode])
                    ) {
                        $this->reasonPhrase = $this->phrases[$this->statusCode];
                    }

                    return $this->reasonPhrase;
                }
            };
        }

        if (class_exists('GuzzleHttp\\Psr7\\Response')) {
            return new class($code, $reasonPhrase) extends \GuzzleHttp\Psr7\Response {
                use ResponseTrait;
                
                public function __construct($code, $reasonPhrase) {
                    parent::__construct($code, [], null, '1.1', $reasonPhrase);
                }
            };
        }

        throw new \RuntimeException('Unable to create a response; default PSR-7 stream libraries not found.');
    }
}
