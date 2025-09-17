<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Models\Reservation;
use Innochannel\Sdk\Exceptions\ApiException;

/**
 * Serviço de Reservas
 * 
 * Gerencia operações relacionadas a reservas no Innochannel
 */
class ReservationService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Criar uma nova reserva
     * 
     * @param array $reservationData Dados da reserva
     * @return Reservation
     * @throws ApiException
     */
    public function create(array $reservationData): Reservation
    {
        $response = $this->client->post('/api/bookings', $reservationData);
        return Reservation::fromArray($response['data']);
    }

    /**
     * Obter uma reserva específica
     * 
     * @param string $reservationId ID da reserva
     * @return Reservation
     * @throws ApiException
     */
    public function get(string $reservationId): Reservation
    {
        $response = $this->client->get("/api/bookings/{$reservationId}");
        return Reservation::fromArray($response['data']);
    }

    /**
     * Listar reservas
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function list(array $filters = []): array
    {
        $response = $this->client->get('/api/bookings', $filters);
        
        $reservations = [];
        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $reservationData) {
                $reservations[] = Reservation::fromArray($reservationData);
            }
        }
        
        return $reservations;
    }

    /**
     * Atualizar uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @param array $updateData Dados para atualização
     * @return Reservation
     * @throws ApiException
     */
    public function update(string $reservationId, array $updateData): Reservation
    {
        $response = $this->client->put("/api/bookings/{$reservationId}", $updateData);
        return Reservation::fromArray($response['data']);
    }

    /**
     * Cancelar uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @param array $cancellationData Dados de cancelamento
     * @return bool
     * @throws ApiException
     */
    public function cancel(string $reservationId, array $cancellationData = []): bool
    {
        $response = $this->client->post("/api/bookings/{$reservationId}/cancel", $cancellationData);
        return $response['success'] ?? false;
    }

    /**
     * Confirmar uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @param array $confirmationData Dados de confirmação
     * @return Reservation
     * @throws ApiException
     */
    public function confirm(string $reservationId, array $confirmationData = []): Reservation
    {
        $response = $this->client->post("/api/bookings/{$reservationId}/confirm", $confirmationData);
        return Reservation::fromArray($response['data']);
    }

    /**
     * Modificar uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @param array $modificationData Dados de modificação
     * @return Reservation
     * @throws ApiException
     */
    public function modify(string $reservationId, array $modificationData): Reservation
    {
        $response = $this->client->post("/api/bookings/{$reservationId}/modify", $modificationData);
        return Reservation::fromArray($response['data']);
    }

    /**
     * Obter histórico de uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @return array
     * @throws ApiException
     */
    public function getHistory(string $reservationId): array
    {
        $response = $this->client->get("/api/bookings/{$reservationId}/history");
        return $response['data'] ?? [];
    }

    /**
     * Sincronizar reserva com PMS
     * 
     * @param string $reservationId ID da reserva
     * @param array $syncOptions Opções de sincronização
     * @return array
     * @throws ApiException
     */
    public function syncWithPms(string $reservationId, array $syncOptions = []): array
    {
        $response = $this->client->post("/api/bookings/{$reservationId}/sync-pms", $syncOptions);
        return $response['data'] ?? [];
    }
}