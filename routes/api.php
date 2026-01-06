<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify', [AuthController::class, 'verifyOtp']);

    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
});
