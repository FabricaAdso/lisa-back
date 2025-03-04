<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Evento que se dispara cuando se genera una nueva notificación.
 * 
 * Implementa ShouldBroadcast para transmitir la notificación en tiempo real a un canal privado.
 */
class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * La notificación que se enviará al usuario.
     * 
     * @var Notification
     */
    public $notification;

    /**
     * Constructor del evento.
     * 
     * @param Notification $notification La notificación que se transmitirá.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        Log::info("Se ha creado una nueva notificación con ID " . $notification->id);
    }

    /**
     * Define el canal privado o publico en el que se transmitirá la notificación.
     * 
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        log::info("Se ha enviado la notificación al canal notifications." . $this->notification->user_id);
        return [
            // Se envía la notificación solo al usuario específico
            new PrivateChannel('notifications.' . $this->notification->user_id),
        ];
    }

    /**
     * Nombre del evento que se transmitirá a los clientes.
     * 
     * @return string
     */
    public function broadcastAs()
    {
        return 'notification.received';
    }

    /**
     * Datos adicionales que se enviarán junto con el evento.
     * 
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'type' => $this->notification->type,
            'data' => $this->notification->data,
            'created_at' => $this->notification->created_at->toISOString(),
        ];
    }
}
