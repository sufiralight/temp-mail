<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/_dev/components', function (Request $request) {
    abort_unless(app()->environment('local') && in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true), 404);

    return view('dev.components');
})->name('dev.components');
