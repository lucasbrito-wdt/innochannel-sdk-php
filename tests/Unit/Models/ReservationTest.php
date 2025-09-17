<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use Innochannel\Sdk\Models\Reservation;
use Innochannel\Sdk\Events\EventManager;
use Innochannel\Sdk\Events\Reservation\ReservationCreatedEvent;
use Innochannel\Sdk\Events\Reservation\ReservationUpdatedEvent;
use Innochannel\Sdk\Events\Reservation\ReservationConfirmedEvent;
use Innochannel\Sdk\Events\Reservation\ReservationCancelledEvent;
use Innochannel\Sdk\Events\Reservation\ReservationDeletedEvent;
use DateTime;

/**
 * Testes para modelo Reservation
 */
class ReservationTest extends TestCase
{
    private array $sampleData;
    private EventManager $eventManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->eventManager = new EventManager();
        Reservation::setEventManager($this->eventManager);
        
        $this->sampleData = [
            'id' => 'reservation-123',
            'property_id' => 'property-456',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '+1234567890',
            'check_in' => '2024-01-15',
            'check_out' => '2024-01-20',
            'adults' => 2,
            'children' => 1,
            'rooms' => 1,
            'status' => 'confirmed',
            'total_amount' => 500.00,
            'currency' => 'USD',
            'source' => 'booking.com',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ];
    }

    public function testReservationCreation(): void
    {
        $reservation = new Reservation($this->sampleData);
        
        $this->assertEquals('reservation-123', $reservation->getId());
        $this->assertEquals('property-456', $reservation->getPropertyId());
        $this->assertEquals('John Doe', $reservation->getGuestName());
        $this->assertEquals('confirmed', $reservation->getStatus());
        $this->assertEquals(500.00, $reservation->getTotalAmount());
    }

    public function testReservationCreatedEvent(): void
    {
        $eventFired = false;
        $this->eventManager->addListener('reservation.created', function() use (&$eventFired) {
            $eventFired = true;
        });

        new Reservation($this->sampleData);
        
        $this->assertTrue($eventFired);
    }

    public function testReservationUpdatedEvent(): void
    {
        $reservation = new Reservation($this->sampleData);
        $eventFired = false;
        
        $this->eventManager->addListener('reservation.updated', function() use (&$eventFired) {
            $eventFired = true;
        });

        $reservation->setGuestName('Jane Doe');
        
        $this->assertTrue($eventFired);
        $this->assertEquals('Jane Doe', $reservation->getGuestName());
    }

    public function testReservationStatusEvents(): void
    {
        $reservation = new Reservation(array_merge($this->sampleData, ['status' => 'pending']));
        $confirmedEventFired = false;
        $cancelledEventFired = false;
        
        $this->eventManager->addListener('reservation.confirmed', function() use (&$confirmedEventFired) {
            $confirmedEventFired = true;
        });

        $reservation->setStatus('confirmed');
        $this->assertTrue($confirmedEventFired);

        $this->eventManager->addListener('reservation.cancelled', function() use (&$cancelledEventFired) {
            $cancelledEventFired = true;
        });

        $reservation->setStatus('cancelled');
        $this->assertTrue($cancelledEventFired);
    }

    public function testReservationDeletedEvent(): void
    {
        $reservation = new Reservation($this->sampleData);
        $eventFired = false;
        
        $this->eventManager->addListener('reservation.deleted', function() use (&$eventFired) {
            $eventFired = true;
        });

        $reservation->delete();
        
        $this->assertTrue($eventFired);
    }

    public function testToArray(): void
    {
        $reservation = new Reservation($this->sampleData);
        $array = $reservation->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('reservation-123', $array['id']);
        $this->assertEquals('John Doe', $array['guest_name']);
    }

    public function testFromArray(): void
    {
        $reservation = Reservation::fromArray($this->sampleData);
        
        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals('reservation-123', $reservation->getId());
        $this->assertEquals('John Doe', $reservation->getGuestName());
    }

    public function testFillFromArray(): void
    {
        $reservation = new Reservation();
        $eventFired = false;
        
        $this->eventManager->addListener('reservation.updated', function() use (&$eventFired) {
            $eventFired = true;
        });

        $reservation->fillFromArray($this->sampleData);
        
        $this->assertTrue($eventFired);
        $this->assertEquals('reservation-123', $reservation->getId());
        $this->assertEquals('John Doe', $reservation->getGuestName());
    }

    public function testDisableEvents(): void
    {
        $reservation = new Reservation($this->sampleData);
        $eventFired = false;
        
        $this->eventManager->addListener('reservation.updated', function() use (&$eventFired) {
            $eventFired = true;
        });

        $reservation->disableEvents();
        $reservation->setGuestName('Jane Doe');
        
        $this->assertFalse($eventFired);
        $this->assertEquals('Jane Doe', $reservation->getGuestName());
    }

    public function testWithoutEvents(): void
    {
        $reservation = new Reservation($this->sampleData);
        $eventFired = false;
        
        $this->eventManager->addListener('reservation.updated', function() use (&$eventFired) {
            $eventFired = true;
        });

        $reservation->withoutEvents(function() use ($reservation) {
            $reservation->setGuestName('Jane Doe');
        });
        
        $this->assertFalse($eventFired);
        $this->assertEquals('Jane Doe', $reservation->getGuestName());
    }

    public function testDirtyTracking(): void
    {
        $reservation = new Reservation($this->sampleData);
        
        $this->assertFalse($reservation->isDirty());
        $this->assertEmpty($reservation->getChanges());

        $reservation->setGuestName('Jane Doe');
        
        $this->assertTrue($reservation->isDirty());
        $changes = $reservation->getChanges();
        $this->assertArrayHasKey('guest_name', $changes);
        $this->assertEquals('John Doe', $changes['guest_name']['old']);
        $this->assertEquals('Jane Doe', $changes['guest_name']['new']);
    }

    public function testDateConversion(): void
    {
        $reservation = new Reservation($this->sampleData);
        
        $this->assertInstanceOf(DateTime::class, $reservation->getCheckIn());
        $this->assertInstanceOf(DateTime::class, $reservation->getCheckOut());
        $this->assertInstanceOf(DateTime::class, $reservation->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $reservation->getUpdatedAt());
    }
}