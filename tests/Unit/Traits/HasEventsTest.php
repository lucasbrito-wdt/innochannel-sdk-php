<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use PHPUnit\Framework\TestCase;
use Innochannel\Sdk\Traits\HasEvents;
use Innochannel\Sdk\Events\EventManager;
use Innochannel\Sdk\Events\EventInterface;

/**
 * Testes para trait HasEvents
 */
class HasEventsTest extends TestCase
{
    private TestModel $model;
    private EventManager $eventManager;
    
    protected function setUp(): void
    {
        $this->eventManager = EventManager::getInstance();
        $this->eventManager->clearListeners();
        $this->eventManager->enable();
        
        $this->model = new TestModel(['name' => 'Test', 'value' => 100]);
    }
    
    protected function tearDown(): void
    {
        $this->eventManager->clearListeners();
        $this->eventManager->enable();
    }
    
    public function testInitializeEvents(): void
    {
        $this->assertNotEmpty($this->model->getOriginalAttributes());
        $this->assertEquals(['name' => 'Test', 'value' => 100], $this->model->getOriginalAttributes());
    }
    
    public function testEnableDisableEvents(): void
    {
        $this->assertTrue($this->model->eventsAreEnabled());
        
        $this->model->disableEvents();
        $this->assertFalse($this->model->eventsAreEnabled());
        
        $this->model->enableEvents();
        $this->assertTrue($this->model->eventsAreEnabled());
    }
    
    public function testIsDirty(): void
    {
        $this->assertFalse($this->model->isDirty());
        
        $this->model->setName('New Name');
        $this->assertTrue($this->model->isDirty());
    }
    
    public function testGetChanges(): void
    {
        $this->assertEmpty($this->model->getChanges());
        
        $this->model->setName('New Name');
        $changes = $this->model->getChanges();
        
        $this->assertArrayHasKey('name', $changes);
        $this->assertEquals('Test', $changes['name']['old']);
        $this->assertEquals('New Name', $changes['name']['new']);
    }
    
    public function testFireEvent(): void
    {
        $eventFired = false;
        $this->eventManager->addListener('test.event', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        $this->model->triggerTestEvent();
        $this->assertTrue($eventFired);
    }
    
    public function testFireEventWhenDisabled(): void
    {
        $eventFired = false;
        $this->eventManager->addListener('test.event', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        $this->model->disableEvents();
        $this->model->triggerTestEvent();
        $this->assertFalse($eventFired);
    }
    
    public function testWithoutEvents(): void
    {
        $eventFired = false;
        $this->eventManager->addListener('test.event', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        $result = $this->model->withoutEvents(function() {
            $this->model->triggerTestEvent();
            return 'callback_result';
        });
        
        $this->assertFalse($eventFired);
        $this->assertEquals('callback_result', $result);
        $this->assertTrue($this->model->eventsAreEnabled()); // Should be re-enabled
    }
    
    public function testSyncOriginalAttributes(): void
    {
        $this->model->setName('Changed');
        $this->assertTrue($this->model->isDirty());
        
        $this->model->syncOriginal();
        $this->assertFalse($this->model->isDirty());
        $this->assertEquals('Changed', $this->model->getOriginalAttributes()['name']);
    }
}

/**
 * Modelo de teste para usar com HasEvents
 */
class TestModel
{
    use HasEvents;
    
    private string $name;
    private int $value;
    
    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->value = $data['value'] ?? 0;
        $this->initializeEvents();
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setValue(int $value): void
    {
        $this->value = $value;
    }
    
    public function getValue(): int
    {
        return $this->value;
    }
    
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value
        ];
    }
    
    public function triggerTestEvent(): void
    {
        $event = $this->createMockEvent('test.event');
        $this->fireEvent($event);
    }
    
    public function syncOriginal(): void
    {
        $this->syncOriginalAttributes();
    }
    
    private function createMockEvent(string $name): EventInterface
    {
        return new class($name) implements EventInterface {
            private string $name;
            
            public function __construct(string $name)
            {
                $this->name = $name;
            }
            
            public function getName(): string
            {
                return $this->name;
            }
            
            public function getData(): array
            {
                return [];
            }
        };
    }
}