<?php

use App\Http\Controllers\InstallationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/_dev/components', function (Request $request) {
    abort_unless(app()->environment('local') && in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true), 404);

    return view('dev.components');
})->name('dev.components');

Route::prefix('install')->name('installation.')->controller(InstallationController::class)->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('/test', 'testConnection')->middleware('throttle:6,1,install-test:')->name('test');
    Route::post('/next', 'next')->middleware('throttle:12,1,install-next:')->name('next');
    Route::post('/commit', 'install')->middleware('throttle:3,1,install-commit:')->name('commit');
});

require __DIR__.'/auth.php';
