<?php

use App\Http\Controllers\AuthController;
use App\Mail\JustificationReminter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
