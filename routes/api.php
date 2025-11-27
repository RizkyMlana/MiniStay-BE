<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/admin/register', [AuthController::class, 'registerAdmin']);
Route::post('/generate-otp', [AuthController::class, 'generateOtp']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'login']);

