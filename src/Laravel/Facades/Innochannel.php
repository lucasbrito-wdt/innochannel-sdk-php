<?php

namespace Innochannel\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade para o cliente Innochannel SDK
 * 
 * Esta facade fornece acesso estático aos métodos do cliente Innochannel,
 * facilitando a integração com sistemas de gerenciamento de propriedades (PMS).
 * 
 * @package Innochannel\Laravel\Facades
 * @author Innochannel SDK Team
 * @version 1.0.0
 * 
 * === MÉTODOS DE RESERVAS ===
 * 
 * @method static \Innochannel\Models\Reservation[] getReservations(array $filters = [])
 *         Obtém uma lista de reservas com filtros opcionais
 *         Parâmetros:
 *         - $filters (array): Filtros para busca (ex: ['status' => 'confirmed', 'date_from' => '2024-01-01'])
 *         Retorna: Array de objetos Reservation
 *         Exemplo: Innochannel::getReservations(['status' => 'confirmed'])
 * 
 * @method static \Innochannel\Models\Reservation getReservation(string $id)
 *         Obtém uma reserva específica pelo ID
 *         Parâmetros:
 *         - $id (string): ID único da reserva
 *         Retorna: Objeto Reservation
 *         Exemplo: Innochannel::getReservation('RES123456')
 * 
 * @method static \Innochannel\Models\Reservation createReservation(array $data)
 *         Cria uma nova reserva
 *         Parâmetros:
 *         - $data (array): Dados da reserva (guest_name, check_in, check_out, room_type, etc.)
 *         Retorna: Objeto Reservation criado
 *         Exemplo: Innochannel::createReservation(['guest_name' => 'João Silva', 'check_in' => '2024-01-15'])
 * 
 * @method static \Innochannel\Models\Reservation updateReservation(string $id, array $data)
 *         Atualiza uma reserva existente
 *         Parâmetros:
 *         - $id (string): ID da reserva a ser atualizada
 *         - $data (array): Dados a serem atualizados
 *         Retorna: Objeto Reservation atualizado
 *         Exemplo: Innochannel::updateReservation('RES123456', ['status' => 'confirmed'])
 * 
 * @method static bool cancelReservation(string $id, array $options = [])
 *         Cancela uma reserva
 *         Parâmetros:
 *         - $id (string): ID da reserva a ser cancelada
 *         - $options (array): Opções de cancelamento (reason, refund_amount, etc.)
 *         Retorna: true se cancelada com sucesso
 *         Exemplo: Innochannel::cancelReservation('RES123456', ['reason' => 'Cliente solicitou'])
 * 
 * @method static array syncReservationWithPms(string $id, array $options = [])
 *         Sincroniza uma reserva com o PMS
 *         Parâmetros:
 *         - $id (string): ID da reserva
 *         - $options (array): Opções de sincronização (direction, force_update, etc.)
 *         Retorna: Array com resultado da sincronização
 *         Exemplo: Innochannel::syncReservationWithPms('RES123456', ['direction' => 'push'])
 * 
 * === MÉTODOS DE PROPRIEDADES ===
 * 
 * @method static \Innochannel\Models\Property[] getProperties(array $filters = [])
 *         Obtém lista de propriedades com filtros opcionais
 *         Parâmetros:
 *         - $filters (array): Filtros de busca (city, country, status, etc.)
 *         Retorna: Array de objetos Property
 *         Exemplo: Innochannel::getProperties(['city' => 'São Paulo'])
 * 
 * @method static \Innochannel\Models\Property getProperty(string $id)
 *         Obtém uma propriedade específica pelo ID
 *         Parâmetros:
 *         - $id (string): ID da propriedade
 *         Retorna: Objeto Property
 *         Exemplo: Innochannel::getProperty('PROP123')
 * 
 * @method static \Innochannel\Models\Property updateProperty(string $id, array $data)
 *         Atualiza dados de uma propriedade
 *         Parâmetros:
 *         - $id (string): ID da propriedade
 *         - $data (array): Dados a serem atualizados (name, address, amenities, etc.)
 *         Retorna: Objeto Property atualizado
 *         Exemplo: Innochannel::updateProperty('PROP123', ['name' => 'Hotel Novo Nome'])
 * 
 * === MÉTODOS DE INVENTÁRIO ===
 * 
 * @method static array getInventory(string $propertyId, array $filters = [])
 *         Obtém inventário de uma propriedade
 *         Parâmetros:
 *         - $propertyId (string): ID da propriedade
 *         - $filters (array): Filtros (date_from, date_to, room_type, etc.)
 *         Retorna: Array com dados de inventário
 *         Exemplo: Innochannel::getInventory('PROP123', ['date_from' => '2024-01-01'])
 * 
 * @method static array updateInventory(string $propertyId, array $data)
 *         Atualiza inventário de uma propriedade
 *         Parâmetros:
 *         - $propertyId (string): ID da propriedade
 *         - $data (array): Dados de inventário (availability, rates, restrictions, etc.)
 *         Retorna: Array com resultado da atualização
 *         Exemplo: Innochannel::updateInventory('PROP123', ['rooms' => [['id' => 'R1', 'availability' => 10]]])
 * 
 * @method static array syncInventoryWithPms(string $propertyId, array $options = [])
 *         Sincroniza inventário com o PMS
 *         Parâmetros:
 *         - $propertyId (string): ID da propriedade
 *         - $options (array): Opções de sincronização (direction, entities, date_range, etc.)
 *         Retorna: Array com resultado da sincronização
 *         Exemplo: Innochannel::syncInventoryWithPms('PROP123', ['direction' => 'pull'])
 * 
 * === MÉTODOS DE WEBHOOKS ===
 * 
 * @method static bool registerWebhook(string $url, array $events = [])
 *         Registra um webhook para receber notificações
 *         Parâmetros:
 *         - $url (string): URL do endpoint que receberá as notificações
 *         - $events (array): Lista de eventos a serem monitorados (reservation.created, inventory.updated, etc.)
 *         Retorna: true se registrado com sucesso
 *         Exemplo: Innochannel::registerWebhook('https://meusite.com/webhook', ['reservation.created'])
 * 
 * @method static bool unregisterWebhook(string $url)
 *         Remove o registro de um webhook
 *         Parâmetros:
 *         - $url (string): URL do webhook a ser removido
 *         Retorna: true se removido com sucesso
 *         Exemplo: Innochannel::unregisterWebhook('https://meusite.com/webhook')
 * 
 * @method static array getWebhooks()
 *         Lista todos os webhooks registrados
 *         Retorna: Array com lista de webhooks configurados
 *         Exemplo: Innochannel::getWebhooks()
 * 
 * === MÉTODOS DE CONEXÃO ===
 * 
 * @method static array testConnection()
 *         Testa a conectividade com a API Innochannel
 *         Retorna: Array com status da conexão e informações do sistema
 *         Exemplo: Innochannel::testConnection()
 * 
 * @method static array syncRates(string $propertyIdInPMS, array $syncOptions = [])
 *         Synchronize rates with the PMS system
 *         Parâmetros:
 *         - $propertyIdInPMS (string): ID da propriedade no PMS
 *         - $syncOptions (array): Opções de sincronização
 * 
 * @see \Innochannel\Client
 * 
 */
class Innochannel extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'innochannel';
    }
}
