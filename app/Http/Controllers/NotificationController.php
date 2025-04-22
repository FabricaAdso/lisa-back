<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function index(){
        $user = User::find(Auth::id());
        $notifications = Notification::where('user_id',$user->id)->get();
        return response()->json($notifications);
    }

    /**
     * Almacena una nueva notificación en la base de datos y la transmite en tiempo real.
     *
     * @param Request $request La solicitud HTTP que contiene los datos de la notificación.
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON.
     */
    public function store(Request $request)
    {
        // Validación de los datos entrantes
        $notification = $request->validate([
            'message' => 'required|string', // El mensaje es obligatorio y debe ser una cadena de texto
            'user_recieved' => 'required|integer|exists:users,id' // Debe ser un ID válido de un usuario existente
        ]);

        // Creación de la notificación en la base de datos
        $data = Notification::create([
            'message' => $notification['message'],
            'user_id' => auth('api')->id(), // Obtiene el ID del usuario autenticado
        ]);

        // Dispara el evento para transmitir la notificación en tiempo real
        event(new NotificationEvent($data));

        // Responde con la notificación creada
        return response()->json([
            'success' => true,
            'message' => 'Notificación enviada correctamente.',
            'notification' => $data
        ]);
    }

    public function update($id) {
        $noti = Notification::findOrFail($id);
        $noti->markAsRead();
        $noti->update(['type' => 'sucess']);
        return response()->json("actualizada correctamente");
    }
}
