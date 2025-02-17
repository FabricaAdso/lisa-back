<?php

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => 'jwt.auth']);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId; // Permite acceso solo al usuario correcto
});