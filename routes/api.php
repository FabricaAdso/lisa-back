<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group([], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

    // Rutas para usuarios
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('usersUpdate/{id}', [UserController::class, 'update']);
    Route::post('users/{id}/deactivate', [UserController::class, 'deactivate']);
    Route::get('deactivated',[UserController::class,'deactivated']);
    Route::get('active', [UserController::class, 'active']);

    // Ruta para gestionar roles
    Route::post('users/{userId}/toggle-role', [RoleController::class, 'toggleRole']);
});

