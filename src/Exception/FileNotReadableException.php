<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Exception;

/**
 * Represents a 404 file not found/readable error.
 */
class FileNotReadableException extends \RuntimeException
{
	/**
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct(sprintf('The file "%s" does not exist or is not readable', $path), 404);
    }
}
