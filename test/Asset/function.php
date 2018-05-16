<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    Phil Bennett <philipobenito@gmail.com>
 * @copyright Copyright (c) 2017 Phil Bennett <philipobenito@gmail.com>
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Test\Asset;

use \Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

/**
 * Named function callable.
 *
 * @param \Psr\Http\Message\ServerRequestInterface $request
 * @param \Psr\Http\Message\ResponseInterface      $response
 *
 * @return \Psr\Http\Message\ResponseInterface
 */
function namedFunctionCallable(ServerRequestInterface $request, ResponseInterface $response) {
    return $response;
}