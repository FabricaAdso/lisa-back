<?php

use App\Http\Controllers\DepartamentController;
use App\Http\Controllers\EnvironmentAreaController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\HeadquartersController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\TrainingCenterController;
use App\Models\EnvironmentArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


    Route::apiresource('departaments',DepartamentController::class);
    Route::apiresource('municipalities',MunicipalityController::class);
    
    Route::apiResource('headquarters', HeadquartersController::class);
    Route::apiresource('environments',EnvironmentController::class);
    Route::apiresource('environmentsArea',EnvironmentAreaController::class);
    Route::apiresource('trainingCenters',TrainingCenterController::class);