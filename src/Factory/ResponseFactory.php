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
    public function createResponse($code = 200): ResponseInterface
    {
        if (class_exists('Zend\\Diactoros\\Response')) {
            return new class($code) extends \Zend\Diactoros\Response {
				use ResponseTrait;
				
				public function __construct($code) {
					parent::__construct('php://memory', $code);
				}
			};
        }

        if (class_exists('GuzzleHttp\\Psr7\\Response')) {
			return new class($code) extends \GuzzleHttp\Psr7\Response {
				use ResponseTrait;
				
				public function __construct($code) {
					parent::__construct($code);
				}
			};
        }

        throw new \RuntimeException('Unable to create a response; default PSR-7 stream libraries not found.');
    }
}
