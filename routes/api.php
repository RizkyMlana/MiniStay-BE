<?php

use App\Http\Controllers\AdminController;
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


Route::prefix('admin')->middleware(['auth:sanctum'])->group( function () {
    Route::get('/rooms', [AdminController::class, 'getRoom']);
    Route::post('/rooms', [AdminController::class, 'createRoom']);
    Route::post('/rooms/{roomId}', [AdminController::class, 'updateRoom']);

    Route::post('/rooms/{roomId}/status', [AdminController::class, 'setRoomStatus']);
    Route::get('/rooms/{roomId}/calendar', [AdminController::class, 'getRoomCalendar']);

    Route::get('/booking', [AdminController::class, 'getBookings']);
    Route::post('/booking/{bookingId}/confirm', [AdminController::class, 'confirmPayment']);
    Route::post('/booking/{bookingId}/cancel', [AdminController::class, 'cancelBooking']);

    Route::get('/reports/revenue', [AdminController::class, 'getRevenueReport']);
});
