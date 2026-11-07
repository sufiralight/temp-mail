<?php

use App\Http\Controllers\InstallationController;
use Illuminate\Support\Facades\Route;

// This read-only entry point must render before Laravel sessions are available.
Route::get('/install', InstallationController::class)->withoutMiddleware('web')->name('install');

Route::get('/', function () {
    return view('welcome');
});
