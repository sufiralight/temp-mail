<?php

use App\Http\Controllers\InstallationController;
use App\Installation\InstallationStore;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(app(InstallationStore::class)->completed() ? '/login' : '/install');
})->name('home');

Route::prefix('install')->name('install.')->middleware('throttle:installation')->group(function (): void {
    Route::get('/', [InstallationController::class, 'welcome'])->name('welcome');
    Route::get('/database', [InstallationController::class, 'database'])->name('database');
    Route::post('/database', [InstallationController::class, 'probe'])->middleware('throttle:installation-write')->name('probe');
    Route::get('/admin', [InstallationController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallationController::class, 'saveAdmin'])->name('admin.save');
    Route::get('/complete', [InstallationController::class, 'review'])->name('review');
    Route::post('/complete', [InstallationController::class, 'complete'])->middleware('throttle:installation-write')->name('complete');
});
Route::view('/login', 'auth.pending')->name('login');
