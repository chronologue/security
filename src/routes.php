<?php

use Chronologue\Security\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::get('/auth/redirect', 'redirect')->name('login');
    Route::get('/auth/callback', 'callback')->name('login.callback');
    Route::post('/logout', 'logout')->name('logout');
});