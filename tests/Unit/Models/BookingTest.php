<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Innochannel\Sdk\Models\Booking;
use Innochannel\Sdk\Events\EventManager;
use Innochannel\Sdk\Events\Booking\BookingCreatedEvent;
use Innochannel\Sdk\Events\Booking\BookingUpdatedEvent;
use Innochannel\Sdk\Events\Booking\BookingConfirmedEvent;
use Innochannel\Sdk\Events\Booking\BookingCancelledEvent;
use Innochannel\Sdk\Events\Booking\BookingDeletedEvent;
use DateTime;

/**
 * Testes para modelo Booking
 */
class BookingTest extends TestCase
{
    private EventManager $eventManager;
    private array $sampleData;
    
    protected function setUp(): void
    {
        $this->eventManager = EventManager::getInstance();
        $this->eventManager->clearListeners();
        $this->eventManager->enable();
        
        $this->sampleData = [
            'id' => 'booking-123',
            'propertyId' => 'property-456',
            'roomId' => 'room-789',
            'ratePlanId' => 'rate-plan-101',
            'guestName' => 'John Doe',
            'guestEmail' => 'john@example.com',
            'guestPhone' => '+1234567890',
            'checkIn' => '2024-01-15',
            'checkOut' => '2024-01-20',
            'adults' => 2,
            'children' => 1,
            'totalAmount' => 500.00,
            'currency' => 'USD',
            'status' => 'confirmed',
            'source' => 'booking.com',
            'confirmationCode' => 'ABC123',
            'specialRequests' => 'Late check-in',
            'createdAt' => '2024-01-01 10:00:00',
            'updatedAt' => '2024-01-01 10:00:00'
        ];
    }
    
    protected function tearDown(): void
    {
        $this->eventManager->clearListeners();
        $this->eventManager->enable();
    }
    
    public function testConstructorWithData(): void
    {
        $booking = new Booking($this->sampleData);
        
        $this->assertEquals('booking-123', $booking->getId());
        $this->assertEquals('property-456', $booking->getPropertyId());
        $this->assertEquals('John Doe', $booking->getGuestName());
        $this->assertEquals('confirmed', $booking->getStatus());
        $this->assertEquals(500.00, $booking->getTotalAmount());
    }
    
    public function testConstructorFiresCreatedEvent(): void
    {
        $eventFired = false;
        $this->eventManager->addListener('booking.created', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        new Booking($this->sampleData);
        
        $this->assertTrue($eventFired);
    }
    
    public function testSettersFireUpdateEvents(): void
    {
        $booking = new Booking($this->sampleData);
        
        $eventFired = false;
        $this->eventManager->addListener('booking.updated', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        $booking->setGuestName('Jane Doe');
        
        $this->assertTrue($eventFired);
        $this->assertEquals('Jane Doe', $booking->getGuestName());
    }
    
    public function testSetStatusFiresSpecificEvents(): void
    {
        $booking = new Booking(array_merge($this->sampleData, ['status' => 'pending']));
        
        $confirmedEventFired = false;
        $this->eventManager->addListener('booking.confirmed', function() use (&$confirmedEventFired) {
            $confirmedEventFired = true;
        });
        
        $booking->setStatus('confirmed');
        $this->assertTrue($confirmedEventFired);
        
        $cancelledEventFired = false;
        $this->eventManager->addListener('booking.cancelled', function() use (&$cancelledEventFired) {
            $cancelledEventFired = true;
        });
        
        $booking->setStatus('cancelled');
        $this->assertTrue($cancelledEventFired);
    }
    
    public function testDeleteFiresDeletedEvent(): void
    {
        $booking = new Booking($this->sampleData);
        
        $eventFired = false;
        $this->eventManager->addListener('booking.deleted', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        $booking->delete();
        
        $this->assertTrue($eventFired);
    }
    
    public function testToArray(): void
    {
        $booking = new Booking($this->sampleData);
        $array = $booking->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('booking-123', $array['id']);
        $this->assertEquals('John Doe', $array['guestName']);
        $this->assertEquals(500.00, $array['totalAmount']);
    }
    
    public function testFromArray(): void
    {
        $booking = Booking::fromArray($this->sampleData);
        
        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals('booking-123', $booking->getId());
        $this->assertEquals('John Doe', $booking->getGuestName());
    }
    
    public function testFillFromArray(): void
    {
        $booking = new Booking();
        
        $eventFired = false;
        $this->eventManager->addListener('booking.updated', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        $booking->fillFromArray($this->sampleData);
        
        $this->assertTrue($eventFired);
        $this->assertEquals('booking-123', $booking->getId());
        $this->assertEquals('John Doe', $booking->getGuestName());
    }
    
    public function testEventsCanBeDisabled(): void
    {
        $booking = new Booking($this->sampleData);
        
        $eventFired = false;
        $this->eventManager->addListener('booking.updated', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        $booking->disableEvents();
        $booking->setGuestName('Jane Doe');
        
        $this->assertFalse($eventFired);
        $this->assertEquals('Jane Doe', $booking->getGuestName());
    }
    
    public function testWithoutEvents(): void
    {
        $booking = new Booking($this->sampleData);
        
        $eventFired = false;
        $this->eventManager->addListener('booking.updated', function() use (&$eventFired) {
            $eventFired = true;
        });
        
        $booking->withoutEvents(function() use ($booking) {
            $booking->setGuestName('Jane Doe');
        });
        
        $this->assertFalse($eventFired);
        $this->assertEquals('Jane Doe', $booking->getGuestName());
    }
    
    public function testIsDirtyAndGetChanges(): void
    {
        $booking = new Booking($this->sampleData);
        
        $this->assertFalse($booking->isDirty());
        $this->assertEmpty($booking->getChanges());
        
        $booking->setGuestName('Jane Doe');
        
        $this->assertTrue($booking->isDirty());
        $changes = $booking->getChanges();
        $this->assertArrayHasKey('guestName', $changes);
        $this->assertEquals('John Doe', $changes['guestName']['old']);
        $this->assertEquals('Jane Doe', $changes['guestName']['new']);
    }
    
    public function testDateTimeHandling(): void
    {
        $booking = new Booking($this->sampleData);
        
        $this->assertInstanceOf(DateTime::class, $booking->getCheckIn());
        $this->assertInstanceOf(DateTime::class, $booking->getCheckOut());
        $this->assertInstanceOf(DateTime::class, $booking->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $booking->getUpdatedAt());
    }
}