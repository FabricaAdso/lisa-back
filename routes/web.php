<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');


