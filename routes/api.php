<?php

use App\Http\Controllers\DepartamentController;
use App\Http\Controllers\EnvironmentAreaController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\HeadquartersController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\TrainingCenterController;
use App\Models\EnvironmentArea;
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


Route::apiResource('headquarters', HeadquartersController::class);
Route::apiresource('environments', EnvironmentController::class);
Route::apiresource('trainingCenters', TrainingCenterController::class);


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
    Route::post('users/{userId}/toggle-role', [RoleController::class, 'toggleRole']);

    //  Rutas para cursos y demas
    Route::resource('educationLevel', EducationLevelController::class);
    Route::resource('programs', ProgramController::class);
    Route::resource('courses', CourseController::class);
    Route::put('courses/{courseId}/shifts', [CourseController::class, 'updateShifts']);
});

Route::post('logout', [AuthController::class, 'logout']);

