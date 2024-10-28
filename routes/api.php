<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\EducationLevelController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ShiftController;
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
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::get('document-type', [AuthController::class, 'getDocument']);

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

    //  Rutas para cursos y demas
    Route::resource('days', DayController::class);
    Route::resource('educationLevel', EducationLevelController::class);
    Route::resource('programs', ProgramController::class);
    Route::resource('courses', CourseController::class);
    Route::put('courses/{courseId}/shifts', [CourseController::class, 'updateShifts']);
    Route::resource('shifts', ShiftController::class);
    Route::put('/shifts/{shiftId}/days', [ShiftController::class, 'assignDaysToShift']);
});

Route::post('logout', [AuthController::class, 'logout']);
