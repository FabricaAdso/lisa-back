<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $message;

    public function __construct($message)
    {
        $this->message=$message;
        Log::info('NotificationEvent created with message: ' . $message);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
{
    Log::info('NotificationEvent created with message onnnnnnnnn: ' . $this->message);
    return [
        new Channel('notifications'),
    ];
}
    
    public function broadcastAs()
    {
        Log::info('NotificationEvent created with message onnnnnnnnn: ' . $this->message);
        return 'notification.event';
    }

    public function test()
{
    event(new NotificationEvent("Prueba desde controlador"));
    return response()->json(['message' => 'Evento enviado']);
}
}
