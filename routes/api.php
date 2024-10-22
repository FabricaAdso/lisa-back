<?php

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

Route::resource('days', DayController::class);
Route::resource('shifts', ShiftController::class);
Route::put('/shifts/{shiftId}/days', [ShiftController::class, 'updateDayShift']);
Route::resource('educationLevel', EducationLevelController::class);
Route::resource('programs', ProgramController::class);
Route::resource('courses', CourseController::class);