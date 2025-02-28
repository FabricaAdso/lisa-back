<?php

use App\Http\Controllers\AuthController;
use App\Mail\JustificationReminter;
use App\Models\Assistance;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');

Route::get('/preview', function () {
    $user = User::find(1);
    $asisstance = Assistance::find(1);
    $response = Mail::to($user->email)->send(new JustificationReminter($user, $asisstance));
    dump($response);
});
