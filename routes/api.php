<?php

use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\HeadquartersController;
use App\Http\Controllers\TrainingCenterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EducationLevelController;
use App\Http\Controllers\ProgramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('document-type', [AuthController::class, 'getDocument']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

    // Rutas para usuarios
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('usersUpdate/{id}', [UserController::class, 'update']);

    //Activar y desactivar usuarios. ver usuarios activos e inactivoa
    Route::post('users/{id}/deactivate', [UserController::class, 'deactivate']);
    Route::get('deactivated', [UserController::class, 'deactivated']);
    Route::get('active', [UserController::class, 'active']);

    // Ruta para gestionar roles
    Route::get('/roles', [RoleController::class, 'getRoles']);
    Route::post('users/{userId}/training-centers/{trainingCenterId}/toggle-role', [RoleController::class, 'toggleRole']);

    //  Rutas para cursos y demas
    Route::resource('educationLevel', EducationLevelController::class);
    Route::resource('programs', ProgramController::class);
    Route::resource('courses', CourseController::class);
    Route::put('courses/{courseId}/shifts', [CourseController::class, 'updateShifts']);

    // Centros de formacion, ambientes y sedes
    Route::apiResource('headquarters', HeadquartersController::class);
    Route::apiresource('environments', EnvironmentController::class);
    Route::apiresource('trainingCenters', TrainingCenterController::class);

    // Centros de formacion del USUARIO
    Route::post('/user/{userId}/add-training-center', [AuthController::class, 'addTrainingCenter']);
    Route::get('/user/{userId}/training-centers', [AuthController::class, 'getUserTrainingCenters']);
    Route::delete('/user/{userId}/remove-training-center', [AuthController::class, 'removeTrainingCenter']);

    // Ruta desencriptar training_center_id del token
    Route::get('/training-center', [AuthController::class, 'getTrainingCenterIdFromToken']);
});

Route::post('logout', [AuthController::class, 'logout']);
