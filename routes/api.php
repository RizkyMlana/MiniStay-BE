<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;


Route::post('/generate-otp', [AuthController::class, 'generateOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::post('/admin/register', [AuthController::class, 'registerAdmin']);
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::get('/room', [HotelController::class, 'index']);
Route::get('/room/{id}', [HotelController::class, 'show']);

Route::get('/room/check-available', [HotelController::class, 'checkAvailabilty']);
Route::get('/room/search', [HotelController::class, 'searchAvailable']);
