<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @author    Lievens Benjamin <l.benjamin185@gmail.com>
 * @copyright Copyright (c) 2017 benliev
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Test;

use \PHPUnit\Framework\TestCase;

use \BitFrame\EventManager\{EventInterface, Event, EventManager};

/**
 * @covers \BitFrame\EventManager\EventManager
 */
class EventManagerTest extends TestCase
{
    /** @var \BitFrame\EventManager\EventManager */
    public $eventManager;
	
    protected function setUp()
    {
        $this->eventManager = new EventManager();
    }
	
    public function testAttach()
    {
        $callback = function ($event) {};
		
        $this->eventManager->attach('test', $callback);
		
        $this->assertSame($callback, $this->eventManager->getListeners('test')[0]['callback']);
    }
	
	public function testEventManager()
	{
		$eventManager = new EventManager();
		$eventManager->attach('create.event', function(EventInterface $event) {
			echo "Received {$event->getParam('title')}.";
		});
		
		$this->expectOutputString('Received Lorem ipsum.');
		
		$event = new Event('create.event', $this, ['title' => 'Lorem ipsum']);
		$eventManager->trigger($event);
	}
		
    public function testDetachListener()
    {
        $callback = function ($event) {};
		
        $this->eventManager->attach('test', $callback);
        $this->eventManager->detach('test', $callback);
		
        $this->assertEmpty($this->eventManager->getListeners('test'));
    }
	
	public function testDetachUnknowEvent()
    {
        $this->assertFalse(
			$this->eventManager->detach('undefined', function () {})
		);
    }
	
	public function testDetachWithDifferentCallbacks()
    {
        $event = new Event('test');
        $this->eventManager->attach($event->getName(), function () {
            echo '1';
        });
		
        $this->assertFalse(
			$this->eventManager->detach($event->getName(), function () {})
		);
    }
	
    public function testClearListenersForEvent()
    {
        $callback = function ($event) {};
		
        $this->eventManager->attach('test', $callback);
        $this->eventManager->attach('test', $callback);
        $this->eventManager->clearListeners('test');
		
        $this->assertEmpty($this->eventManager->getListeners('test'));
    }
	
    public function testClearAllListeners()
    {
        $callback = function ($event) {};
		
        $this->eventManager->attach('test', $callback);
        $this->eventManager->attach('test', $callback);
        $this->eventManager->clearListeners();
		
        $this->assertEmpty($this->eventManager->getListeners('test'));
    }
	
    public function testTrigger()
    {
        $testOk = false;
        $callback = function ($event) use (&$testOk) {
            $testOk = true;
        };
		
        $this->eventManager->attach('test', $callback);
        $this->eventManager->trigger('test');
		
        $this->assertTrue($testOk);
    }
	
    public function testTriggerUndefined()
    {
        $testOk = false;
        $callback = function ($event) use (&$testOk) {
            $testOk = true;
        };
		
        $this->eventManager->attach('test', $callback);
        $this->eventManager->trigger('undefinedEvent');
		
        $this->assertFalse($testOk);
    }
	
	public function testTriggerWithoutEventObject()
    {
        $this->eventManager->attach('test', function (EventInterface $event) {
            echo 'Works!';
			
			$this->assertSame(['test' => '123'], $event->getParams());
        });
		
        $this->expectOutputString('Works!');
		
        $this->eventManager->trigger('test', $this, ['test' => '123']);
    }
	
	public function testTriggerWithEventObject()
    {
        $this->eventManager->attach('test', function (EventInterface $event) {
            echo 'Works!';
			
			$this->assertSame(['test' => '456', 'hello' => 'world'], $event->getParams());
			$this->assertSame($this, $event->getTarget());
        });
		
        $this->expectOutputString('Works!');
		
        $this->eventManager->trigger(new Event('test', null, ['test' => '123']), $this, ['test' => '456', 'hello' => 'world']);
    }
	
	public function testTriggerOrderWithPriority()
    {
        $event = new Event('test');
		
        $this->eventManager->attach($event->getName(), function () {
            echo '1';
        }, 3);
		
        $this->eventManager->attach($event->getName(), function () {
            echo '3';
        }, 1);
		
        $this->eventManager->attach($event->getName(), function () {
            echo '2';
        }, 2);
		
        $this->expectOutputString('321');
		
        $this->eventManager->trigger($event);
    }
	
	public function testTriggerWithStopPropagation()
    {
        $event = new Event('test');
		
        $this->eventManager->attach($event->getName(), function () {
            echo '1';
        });
		
        $this->eventManager->attach($event->getName(), function (EventInterface $event) {
            $event->stopPropagation(true);
			echo '2';
        });
		
        $this->eventManager->attach($event->getName(), function () {
            echo '3';
        });
		
        $this->expectOutputString('12');
        $this->eventManager->trigger($event);
    }
	
    public function testListeners()
    {
        $callback = function ($event) {};
		
        $this->eventManager->attach('test', $callback);
		
        $this->assertEquals($callback, $this->eventManager->getListeners('test')[0]['callback']);
    }
}