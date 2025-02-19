<?php

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Broadcast;

/**
 * Configura las rutas de transmisión con autenticación JWT.
 */
Broadcast::routes(['middleware' => 'jwt.auth']);

/**
 * Canal de autenticación de usuario en tiempo real.
 * 
 * Se asegura de que solo el usuario autenticado pueda escuchar eventos en su propio canal.
 */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Canal privado de notificaciones para cada usuario.
 * 
 * Solo el usuario con el ID correspondiente puede escuchar las notificaciones enviadas a su canal.
 */
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
