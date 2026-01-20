<?php

use App\Http\Controllers\AdminRatingController;
use App\Http\Controllers\AdminRoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomAvailabilityController;
use App\Http\Controllers\RoomBlockController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomImageController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify', [AuthController::class, 'verifyOtp']);

    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
});


Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);
Route::get('/rooms/{id}/availability', [RoomAvailabilityController::class, 'show']);
Route::get('/rooms/{roomId}/ratings', [RatingController::class, 'roomRatings']);

Route::middleware('auth:sanctum')->group(function () {

    // CHAT (booking-scoped user & admin)
    Route::get('/messages', [ChatController::class, 'index']);
    Route::post('/messages', [ChatController::class, 'store']);
    Route::post('/messages/{id}/read', [ChatController::class, 'markAsRead']);

    // BOOKINGS (USER)
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/me', [BookingController::class, 'myBookings']);
    Route::post('/bookings/{id}/pay', [PaymentController::class, 'submit']);

    // RATINGS
    Route::post('/ratings', [RatingController::class, 'store']);

});

Route::middleware(['auth:sanctum', RoleMiddleware::class. ':admin'])
    ->prefix('admin')
    ->group(function () {

    // ROOMS
    Route::post('/rooms', [AdminRoomController::class, 'store']);
    Route::put('/rooms/{id}', [AdminRoomController::class, 'update']);
    Route::delete('/rooms/{id}', [AdminRoomController::class, 'destroy']);

    // ROOM IMAGES
    Route::post('/rooms/{roomId}/images', [RoomImageController::class, 'store']);
    Route::post('/room-images/{id}/cover', [RoomImageController::class, 'setCover']);
    Route::delete('/room-images/{id}', [RoomImageController::class, 'destroy']);

    // ROOM BLOCKS
    Route::post('/room-blocks', [RoomBlockController::class, 'store']);
    Route::get('/room-date/{roomId}', [RoomBlockController::class, 'getRoomBlocks']);
    Route::delete('/room-date/{id}', [RoomBlockController::class, 'destroy']);

    // BOOKINGS (ADMIN)
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::put('/bookings/{id}/status', [BookingController::class, 'cancel']);

    // PAYMENTS
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm']);
    Route::post('/payments/{id}/reject', [PaymentController::class, 'reject']);

    // CHECK-IN
    Route::post('/checkin', [CheckinController::class, 'checkin']);

    // RATINGS MODERATION
    Route::get('/ratings', [AdminRatingController::class, 'index']);
    Route::post('/ratings/{id}/toggle', [AdminRatingController::class, 'toggleVisibility']);

    // REPORTS
    Route::prefix('reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily']);
        Route::get('/weekly', [ReportController::class, 'weekly']);
        Route::get('/monthly', [ReportController::class, 'monthly']);
        Route::get('/top-rooms', [ReportController::class, 'topRooms']);
    });

});
