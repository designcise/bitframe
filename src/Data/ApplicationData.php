<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Data;

/**
 * Stores application data to share across all middleware.
 */
class ApplicationData implements \ArrayAccess
{
	use ApplicationDataTrait;
	
	/** @var array */
    private $data;
	
	/**
     * @param array $data (optional)
     */
    public function __construct(array $data = [])
    {
		$this->data = $data;
    }
	
	/**
     * Get stored application data.
     *
     * @return mixed[]
     */
	public function getData(): array
	{
		return $this->data;
	}
}