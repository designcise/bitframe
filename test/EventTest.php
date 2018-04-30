<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Test;

use \PHPUnit\Framework\TestCase;

use \BitFrame\EventManager\Event;

/**
 * @covers \BitFrame\EventManager\Event
 */
class EventTest extends TestCase
{
    /** @var \BitFrame\Event\Event */
    public $event;
	
    protected function setUp()
    {
        $this->event = new Event('test', null, ['key0' => 'val0'], false);
    }
	
	public function testEventCanSetAndGetAllProperties()
    {
        $name = 'test2';
		$this->assertSame($name, $this->event->setName($name)->getName());
		
        $target = $this;
		$this->assertSame($target, $this->event->setTarget($target)->getTarget());
		
		$params = ['key1' => 'val1', 'key2' => 'val2'];
		$this->assertSame($params, $this->event->setParams($params)->getParams());
		
        $propagate = false;
		$this->assertSame($propagate, $this->event->stopPropagation($propagate)->isPropagationStopped());
    }
}