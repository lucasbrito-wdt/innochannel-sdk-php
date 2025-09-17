<?php

declare(strict_types=1);

namespace Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use Innochannel\Sdk\Events\EventManager;
use Innochannel\Sdk\Events\EventInterface;

/**
 * Testes para EventManager
 */
class EventManagerTest extends TestCase
{
    private EventManager $eventManager;
    
    protected function setUp(): void
    {
        $this->eventManager = EventManager::getInstance();
        $this->eventManager->clearListeners();
        $this->eventManager->enable();
    }
    
    protected function tearDown(): void
    {
        $this->eventManager->clearListeners();
        $this->eventManager->enable();
    }
    
    public function testSingletonInstance(): void
    {
        $instance1 = EventManager::getInstance();
        $instance2 = EventManager::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }
    
    public function testAddListener(): void
    {
        $called = false;
        $listener = function() use (&$called) {
            $called = true;
        };
        
        $this->eventManager->addListener('test.event', $listener);
        
        $event = $this->createMockEvent('test.event');
        $this->eventManager->fire($event);
        
        $this->assertTrue($called);
    }
    
    public function testRemoveListener(): void
    {
        $called = false;
        $listener = function() use (&$called) {
            $called = true;
        };
        
        $this->eventManager->addListener('test.event', $listener);
        $this->eventManager->removeListener('test.event', $listener);
        
        $event = $this->createMockEvent('test.event');
        $this->eventManager->fire($event);
        
        $this->assertFalse($called);
    }
    
    public function testClearListeners(): void
    {
        $called = false;
        $listener = function() use (&$called) {
            $called = true;
        };
        
        $this->eventManager->addListener('test.event', $listener);
        $this->eventManager->clearListeners();
        
        $event = $this->createMockEvent('test.event');
        $this->eventManager->fire($event);
        
        $this->assertFalse($called);
    }
    
    public function testDisableEvents(): void
    {
        $called = false;
        $listener = function() use (&$called) {
            $called = true;
        };
        
        $this->eventManager->addListener('test.event', $listener);
        $this->eventManager->disable();
        
        $event = $this->createMockEvent('test.event');
        $this->eventManager->fire($event);
        
        $this->assertFalse($called);
    }
    
    public function testEnableEvents(): void
    {
        $called = false;
        $listener = function() use (&$called) {
            $called = true;
        };
        
        $this->eventManager->addListener('test.event', $listener);
        $this->eventManager->disable();
        $this->eventManager->enable();
        
        $event = $this->createMockEvent('test.event');
        $this->eventManager->fire($event);
        
        $this->assertTrue($called);
    }
    
    public function testWithoutEvents(): void
    {
        $called = false;
        $listener = function() use (&$called) {
            $called = true;
        };
        
        $this->eventManager->addListener('test.event', $listener);
        
        $result = $this->eventManager->withoutEvents(function() use (&$called) {
            $event = $this->createMockEvent('test.event');
            $this->eventManager->fire($event);
            return 'test_result';
        });
        
        $this->assertFalse($called);
        $this->assertEquals('test_result', $result);
    }
    
    public function testMultipleListeners(): void
    {
        $called1 = false;
        $called2 = false;
        
        $listener1 = function() use (&$called1) {
            $called1 = true;
        };
        
        $listener2 = function() use (&$called2) {
            $called2 = true;
        };
        
        $this->eventManager->addListener('test.event', $listener1);
        $this->eventManager->addListener('test.event', $listener2);
        
        $event = $this->createMockEvent('test.event');
        $this->eventManager->fire($event);
        
        $this->assertTrue($called1);
        $this->assertTrue($called2);
    }
    
    public function testEventWithData(): void
    {
        $receivedData = null;
        $listener = function(EventInterface $event) use (&$receivedData) {
            $receivedData = $event->getData();
        };
        
        $this->eventManager->addListener('test.event', $listener);
        
        $testData = ['key' => 'value'];
        $event = $this->createMockEvent('test.event', $testData);
        $this->eventManager->fire($event);
        
        $this->assertEquals($testData, $receivedData);
    }
    
    private function createMockEvent(string $name, array $data = []): EventInterface
    {
        $event = $this->createMock(EventInterface::class);
        $event->method('getName')->willReturn($name);
        $event->method('getData')->willReturn($data);
        
        return $event;
    }
}