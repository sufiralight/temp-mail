<?php

use App\Http\Controllers\AuthenticationController as Auth;
use App\Http\Middleware\AuthenticationAvailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(AuthenticationAvailable::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::view('/login', 'auth.login')->name('login');
        Route::post('/login', [Auth::class, 'login'])->middleware('throttle:8,1,login:');
        Route::view('/register', 'auth.register')->name('register');
        Route::post('/register', [Auth::class, 'register'])->middleware('throttle:5,1,register:');
        Route::view('/forgot-password', 'auth.forgot')->name('password.request');
        Route::post('/forgot-password', [Auth::class, 'forgot'])->middleware('throttle:5,1,forgot:')->name('password.email');
        Route::get('/reset-password/{token}', function (Request $request, string $token) {
            $email = $request->query('email');

            return view('auth.reset', ['token' => $token, 'email' => is_string($email) ? $email : '']);
        })->name('password.reset');
        Route::post('/reset-password', [Auth::class, 'reset'])->middleware('throttle:8,1,reset:')->name('password.update');
    });

    Route::middleware(['auth', 'auth.session'])->group(function () {
        Route::post('/logout', [Auth::class, 'logout'])->name('logout');
        Route::view('/email/verify', 'auth.verify')->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', [Auth::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1,verify:'])->name('verification.verify');
        Route::post('/email/verification-notification', [Auth::class, 'resend'])
            ->middleware('throttle:3,1,resend:')->name('verification.send');
        Route::get('/account', function (Request $request) {
            if (! $request->user()->hasVerifiedEmail() && ! $request->user()->can('super-admin')) {
                return redirect()->route('verification.notice');
            }

            return view('auth.account');
        })->name('account');
    });
});
