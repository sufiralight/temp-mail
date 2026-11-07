<?php

use App\Http\Controllers\FirstInstallationController;
use App\Http\Controllers\InstallationController;
use App\Http\Middleware\StartInstallationSession;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware('web')->middleware(StartInstallationSession::class)->group(function (): void {
    Route::get('/install', InstallationController::class)->name('install');
    Route::post('/install/database', [FirstInstallationController::class, 'testDatabase'])->name('install.database');
    Route::get('/install/admin', [FirstInstallationController::class, 'admin'])->name('install.admin');
    Route::post('/install/admin', [FirstInstallationController::class, 'finish'])->name('install.finish');
});

Route::get('/login', [FirstInstallationController::class, 'handoff'])->withoutMiddleware('web')->name('login');

Route::get('/', function () {
    return view('welcome');
});
