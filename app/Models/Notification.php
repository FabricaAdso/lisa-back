<?php

namespace App\Models;

use App\Events\SendMessage;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['message'];
    protected $with = ['user'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }   

    // Función para crear y disparar el evento
    public static function createAndSendMessage($data)
    {
        // Crea una nueva notificación
        $message = Notification::create($data);

        // Dispara el evento SendMessage
        event(new SendMessage($message));

        return $message;
    }

}
