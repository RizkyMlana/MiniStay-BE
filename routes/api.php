<?php

use App\Http\Controllers\AdminRoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoomAvailabilityController;
use App\Http\Controllers\RoomBlockController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify', [AuthController::class, 'verifyOtp']);

    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
});

Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'Show']);

Route::get('/rooms/{id}/availability', [RoomAvailabilityController::class, 'show']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/admin/rooms', [AdminRoomController::class, 'store']);
    Route::put('/admin/rooms/{id}', [AdminRoomController::class, 'update']);
    Route::delete('/admin/rooms/{id}', [AdminRoomController::class, 'destroy']);

    Route::post('/admin/rooms/{roomId}/images', [RoomImageController::class, 'store']);
    Route::post('/admin/room-images/{id}/cover', [RoomImageController::class, 'setCover']);
    Route::delete('/admin/room-images/{id}', [RoomImageController::class, 'destroy']);

    Route::post('/admin/room-blocks', [RoomBlockController::class, 'store']);


    Route::get('/admin/bookings', [BookingController::class, 'index']);
    Route::post('/admin/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    Route::get('/admin/payments', [PaymentController::class, 'index']);
    Route::post('/admin/payments/{id}/confirm', [PaymentController::class, 'confirm']);
    Route::post('/admin/payments/{id}/reject', [PaymentController::class, 'reject']);

    Route::post('/admin/checkin', [CheckinController::class, 'checkin']);

    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/mybooking', [BookingController::class, 'myBookings']);

    Route::post('/bookings/{id}/pay', [PaymentController::class, 'submit']);

});