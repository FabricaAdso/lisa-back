<?php

use App\Http\Controllers\AssistanceController;
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
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ShiftController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiresource('departaments', DepartamentController::class);
Route::apiresource('municipalities', MunicipalityController::class);

Route::get('municipalities/departament/{id}', [MunicipalityController::class, 'index']);

Route::apiResource('headquarters', HeadquartersController::class);
Route::apiresource('environments', EnvironmentController::class);
Route::apiresource('environmentsArea', EnvironmentAreaController::class);
Route::apiresource('trainingCenters', TrainingCenterController::class);


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
    Route::get('deactivated', [UserController::class, 'deactivated']);
    Route::get('active', [UserController::class, 'active']);

    // Ruta para gestionar roles
    Route::post('users/{userId}/toggle-role', [RoleController::class, 'toggleRole']);
});

Route::resource('days', DayController::class);
Route::resource('educationLevel', EducationLevelController::class);
Route::resource('programs', ProgramController::class);
Route::resource('courses', CourseController::class);
Route::put('courses/{courseId}/shifts', [CourseController::class, 'updateShifts']);
Route::resource('shifts', ShiftController::class);
Route::put('/shifts/{shiftId}/days', [ShiftController::class, 'assignDaysToShift']);

// Rutas Participantes

Route::post('participants', [ParticipantController::class, 'assignParticipants']);
Route::get('participants', [ParticipantController::class, 'getParticipants']);
Route::post('participants/assign-instructor', [ParticipantController::class, 'assignInstructor']);
Route::post('participants/assign-aprendiz', [ParticipantController::class, 'assignAprendiz']);
Route::post('/participants/{id}/assign-role', [ParticipantController::class, 'assignRoleToParticipant']);
Route::post('sessions', [SessionController::class, 'createSession']);
Route::get('participants', [ParticipantController::class, 'getParticipantsByRole']);
Route::post('/sessions/{sessionId}/assistances', [AssistanceController::class, 'createForSession']);
Route::put('/assistance/{assistanceId}', [AssistanceController::class, 'editAssistance']);



