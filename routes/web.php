<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/privacy-policy', function () {
    return view('privacy');
});

Route::get('/terms-of-service', function () {
    return view('terms');
});
