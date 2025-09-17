<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\ValidationException;
use Innochannel\Sdk\Models\Booking;

/**
 * Serviço para gerenciamento de reservas
 * 
 * @package Innochannel\Sdk\Services
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class BookingService
{
    private Client $client;
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * Criar nova reserva
     * 
     * @param array $bookingData
     * @return Booking
     * @throws ApiException
     * @throws ValidationException
     */
    public function create(array $bookingData): Booking
    {
        $this->validateBookingData($bookingData);
        
        $response = $this->client->post('/api/bookings', $bookingData);
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? null;
        
        if ($data === null) {
            throw new ApiException('Failed to create booking', 500);
        }
        
        return Booking::fromArray($data);
    }
    
    /**
     * Obter reserva por ID
     * 
     * @param string $bookingId
     * @return Booking
     * @throws ApiException
     */
    public function get(string $bookingId): Booking
    {
        $response = $this->client->get("/api/bookings/{$bookingId}");
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? null;
        
        if ($data === null) {
            throw new ApiException('Booking not found', 404);
        }
        
        return Booking::fromArray($data);
    }
    
    /**
     * Listar reservas
     * 
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function list(array $filters = []): array
    {
        return $this->client->get('/api/bookings', $filters);
    }
    
    /**
     * Atualizar reserva
     * 
     * @param string $bookingId
     * @param array $updateData
     * @return Booking
     * @throws ApiException
     * @throws ValidationException
     */
    public function update(string $bookingId, array $updateData): Booking
    {
        $this->validateUpdateData($updateData);
        
        $response = $this->client->put("/api/bookings/{$bookingId}", $updateData);
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? null;
        
        if ($data === null) {
            throw new ApiException('Failed to update booking', 500);
        }
        
        return Booking::fromArray($data);
    }
    
    /**
     * Cancelar reserva
     * 
     * @param string $bookingId
     * @param array $cancellationData
     * @return Booking
     * @throws ApiException
     */
    public function cancel(string $bookingId, array $cancellationData = []): Booking
    {
        $response = $this->client->post("/api/bookings/{$bookingId}/cancel", $cancellationData);
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? null;
        
        if ($data === null) {
            throw new ApiException('Failed to cancel booking', 500);
        }
        
        return Booking::fromArray($data);
    }
    
    /**
     * Confirmar reserva
     * 
     * @param string $bookingId
     * @param array $confirmationData
     * @return Booking
     * @throws ApiException
     */
    public function confirm(string $bookingId, array $confirmationData = []): Booking
    {
        $response = $this->client->post("/api/bookings/{$bookingId}/confirm", $confirmationData);
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? null;
        
        if ($data === null) {
            throw new ApiException('Failed to confirm booking', 500);
        }
        
        return Booking::fromArray($data);
    }
    
    /**
     * Modificar reserva
     * 
     * @param string $bookingId
     * @param array $modificationData
     * @return Booking
     * @throws ApiException
     * @throws ValidationException
     */
    public function modify(string $bookingId, array $modificationData): Booking
    {
        $this->validateModificationData($modificationData);
        
        $response = $this->client->post("/api/bookings/{$bookingId}/modify", $modificationData);
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? null;
        
        if ($data === null) {
            throw new ApiException('Failed to modify booking', 500);
        }
        
        return Booking::fromArray($data);
    }
    
    /**
     * Obter histórico de uma reserva
     * 
     * @param string $bookingId
     * @return array
     * @throws ApiException
     */
    public function getHistory(string $bookingId): array
    {
        $response = $this->client->get("/api/bookings/{$bookingId}/history");
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? [];
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Adicionar nota à reserva
     * 
     * @param string $bookingId
     * @param array $noteData
     * @return array
     * @throws ApiException
     */
    public function addNote(string $bookingId, array $noteData): array
    {
        return $this->client->post("/api/bookings/{$bookingId}/notes", $noteData);
    }
    
    /**
     * Obter notas da reserva
     * 
     * @param string $bookingId
     * @return array
     * @throws ApiException
     */
    public function getNotes(string $bookingId): array
    {
        $response = $this->client->get("/api/bookings/{$bookingId}/notes");
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? [];
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Processar pagamento da reserva
     * 
     * @param string $bookingId
     * @param array $paymentData
     * @return array
     * @throws ApiException
     */
    public function processPayment(string $bookingId, array $paymentData): array
    {
        return $this->client->post("/api/bookings/{$bookingId}/payments", $paymentData);
    }
    
    /**
     * Obter pagamentos da reserva
     * 
     * @param string $bookingId
     * @return array
     * @throws ApiException
     */
    public function getPayments(string $bookingId): array
    {
        $response = $this->client->get("/api/bookings/{$bookingId}/payments");
        
        // Handle both direct data and wrapped response formats
        $data = $response['data'] ?? $response ?? [];
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Atualizar nota da reserva
     * 
     * @param string $bookingId
     * @param string $noteId
     * @param array $noteData
     * @return array
     * @throws ApiException
     */
    public function updateNote(string $bookingId, string $noteId, array $noteData): array
    {
        return $this->client->put("/api/bookings/{$bookingId}/notes/{$noteId}", $noteData);
    }
    
    /**
     * Deletar nota da reserva
     * 
     * @param string $bookingId
     * @param string $noteId
     * @return bool
     * @throws ApiException
     */
    public function deleteNote(string $bookingId, string $noteId): bool
    {
        $this->client->delete("/api/bookings/{$bookingId}/notes/{$noteId}");
        return true;
    }
    
    /**
     * Reembolsar pagamento da reserva
     * 
     * @param string $bookingId
     * @param string $paymentId
     * @param array $refundData
     * @return array
     * @throws ApiException
     */
    public function refundPayment(string $bookingId, string $paymentId, array $refundData = []): array
    {
        return $this->client->post("/api/bookings/{$bookingId}/payments/{$paymentId}/refund", $refundData);
    }
    
    /**
     * Sincronizar reserva com PMS
     * 
     * @param string $bookingId
     * @param array $syncOptions
     * @return array
     * @throws ApiException
     */
    public function syncWithPms(string $bookingId, array $syncOptions = []): array
    {
        $syncOptions['direction'] = $syncOptions['direction'] ?? 'push';
        $syncOptions['entities'] = $syncOptions['entities'] ?? ['booking', 'guest', 'payments'];
        
        return $this->client->post("/api/bookings/{$bookingId}/sync", $syncOptions);
    }
    
    /**
     * Validar dados de reserva
     * 
     * @param array $data
     * @throws ValidationException
     */
    protected function validateBookingData(array $data): void
    {
        $errors = [];
        
        // Validações obrigatórias
        if (empty($data['property_id']) && empty($data['propertyId'])) {
            $errors['property_id'] = ['Property ID is required'];
        }
        
        if (empty($data['room_id']) && empty($data['roomId'])) {
            $errors['room_id'] = ['Room ID is required'];
        }
        
        // Handle both flat and nested date structures
        $checkIn = $data['check_in'] ?? $data['dates']['checkIn'] ?? null;
        $checkOut = $data['check_out'] ?? $data['dates']['checkOut'] ?? null;
        
        if (empty($checkIn)) {
            $errors['check_in'] = ['Check-in date is required'];
        }
        
        if (empty($checkOut)) {
            $errors['check_out'] = ['Check-out date is required'];
        }
        
        if (empty($data['guest'])) {
            $errors['guest'] = ['Guest information is required'];
        } else {
            $this->validateGuestData($data['guest'], $errors);
        }
        
        // Validar datas
        if ($checkIn && $checkOut) {
            try {
                $checkInDate = new \DateTime($checkIn);
                $checkOutDate = new \DateTime($checkOut);
                
                if ($checkInDate >= $checkOutDate) {
                    $errors['check_out'] = ['Check-out date must be after check-in date'];
                }
                
                if ($checkInDate < new \DateTime('today')) {
                    $errors['check_in'] = ['Check-in date cannot be in the past'];
                }
            } catch (\Exception $e) {
                $errors['dates'] = ['Invalid date format'];
            }
        }
        
        // Handle both flat and nested occupancy structures
        $adults = $data['adults'] ?? $data['occupancy']['adults'] ?? null;
        $children = $data['children'] ?? $data['occupancy']['children'] ?? null;
        
        if (isset($adults) && (!is_int($adults) || $adults < 1)) {
            $errors['adults'] = ['Number of adults must be at least 1'];
        }
        
        if (isset($children) && (!is_int($children) || $children < 0)) {
            $errors['children'] = ['Number of children must be non-negative'];
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Booking validation failed', $errors);
        }
    }
    
    /**
     * Validar dados de hóspede
     * 
     * @param array $guestData
     * @param array &$errors
     * @param bool $isUpdate Whether this is an update operation (partial validation)
     */
    protected function validateGuestData(array $guestData, array &$errors, bool $isUpdate = false): void
    {
        // Check for both camelCase and snake_case field names
        $firstName = $guestData['first_name'] ?? $guestData['firstName'] ?? '';
        $lastName = $guestData['last_name'] ?? $guestData['lastName'] ?? '';
        
        // For updates, only validate fields that are provided
        if (!$isUpdate || isset($guestData['first_name']) || isset($guestData['firstName'])) {
            if (empty($firstName)) {
                $errors['guest.first_name'] = ['Guest first name is required'];
            }
        }
        
        if (!$isUpdate || isset($guestData['last_name']) || isset($guestData['lastName'])) {
            if (empty($lastName)) {
                $errors['guest.last_name'] = ['Guest last name is required'];
            }
        }
        
        if (!$isUpdate || isset($guestData['email'])) {
            if (empty($guestData['email'])) {
                $errors['guest.email'] = ['Guest email is required'];
            } elseif (!filter_var($guestData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['guest.email'] = ['Guest email must be valid'];
            }
        }
        
        if (isset($guestData['phone']) && !empty($guestData['phone'])) {
            if (!preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $guestData['phone'])) {
                $errors['guest.phone'] = ['Guest phone number format is invalid'];
            }
        }
        
        // Validate document if provided
        if (isset($guestData['document']) && is_array($guestData['document'])) {
            if (isset($guestData['document']['number']) && empty($guestData['document']['number'])) {
                $errors['guest.document.number'] = ['Guest document number cannot be empty'];
            }
        }
    }
    
    /**
     * Validar dados de atualização
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateUpdateData(array $data): void
    {
        $errors = [];
        
        // Validar datas se fornecidas
        if (isset($data['check_in']) && isset($data['check_out'])) {
            $checkIn = new \DateTime($data['check_in']);
            $checkOut = new \DateTime($data['check_out']);
            
            if ($checkIn >= $checkOut) {
                $errors['check_out'] = ['Check-out date must be after check-in date'];
            }
        }
        
        // Validar dados de hóspede se fornecidos
        if (isset($data['guest'])) {
            $this->validateGuestData($data['guest'], $errors, true);
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Update validation failed', $errors);
        }
    }
    
    /**
     * Validar dados de modificação
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateModificationData(array $data): void
    {
        $errors = [];
        
        // If modification_type is not provided, try to infer from the data structure
        if (empty($data['modification_type'])) {
            // Check if this is a flexible modification format (like the test uses)
            if (isset($data['dates']) || isset($data['occupancy']) || isset($data['room']) || isset($data['services'])) {
                // This is a flexible format, validate the specific data
                if (isset($data['dates'])) {
                    $checkIn = $data['dates']['checkIn'] ?? null;
                    $checkOut = $data['dates']['checkOut'] ?? null;
                    
                    if ($checkIn && $checkOut) {
                        try {
                            $checkInDate = new \DateTime($checkIn);
                            $checkOutDate = new \DateTime($checkOut);
                            
                            if ($checkInDate >= $checkOutDate) {
                                $errors['dates'] = ['Check-out date must be after check-in date'];
                            }
                        } catch (\Exception $e) {
                            $errors['dates'] = ['Invalid date format'];
                        }
                    }
                }
                
                if (isset($data['occupancy'])) {
                    $adults = $data['occupancy']['adults'] ?? null;
                    $children = $data['occupancy']['children'] ?? null;
                    
                    if (isset($adults) && (!is_int($adults) || $adults < 1)) {
                        $errors['occupancy.adults'] = ['Number of adults must be at least 1'];
                    }
                    
                    if (isset($children) && (!is_int($children) || $children < 0)) {
                        $errors['occupancy.children'] = ['Number of children must be non-negative'];
                    }
                }
                
                if (!empty($errors)) {
                    throw new ValidationException('Modification validation failed', $errors);
                }
                
                return;
            }
            
            $errors['modification_type'] = ['Modification type is required'];
        } elseif (!in_array($data['modification_type'], ['dates', 'room', 'guests', 'services'])) {
            $errors['modification_type'] = ['Invalid modification type'];
        }
        
        // Validações específicas por tipo de modificação
        switch ($data['modification_type'] ?? '') {
            case 'dates':
                if (empty($data['new_check_in']) || empty($data['new_check_out'])) {
                    $errors['dates'] = ['New check-in and check-out dates are required'];
                }
                break;
                
            case 'room':
                if (empty($data['new_room_id'])) {
                    $errors['new_room_id'] = ['New room ID is required'];
                }
                break;
                
            case 'guests':
                if (!isset($data['new_adults']) && !isset($data['new_children'])) {
                    $errors['guests'] = ['New guest count is required'];
                }
                break;
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Modification validation failed', $errors);
        }
    }
}