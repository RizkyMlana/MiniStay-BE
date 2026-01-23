<?php

use App\Http\Controllers\AdminRatingController;
use App\Http\Controllers\AdminRoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
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
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/me', [BookingController::class, 'myBookings']);

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
    Route::get('/rooms/{roomId}/images', [RoomImageController::class, 'index']);
    Route::post('/room/{id}/cover', [RoomImageController::class, 'setCover']);
    Route::delete('/rooms/{id}/images', [RoomImageController::class, 'destroy']);
    Route::put('/rooms/{roomId}/images', [RoomImageController::class, 'update']);

    // ROOM BLOCKS
    // Route::post('/room-blocks', [RoomBlockController::class, 'store']);
    Route::get('/room-date/{roomId}', [RoomBlockController::class, 'getRoomBlocks']);
    // Route::delete('/room-date/{id}', [RoomBlockController::class, 'destroy']);


    Route::get('/calendar', [CalendarController::class, 'calendar']);
    Route::post('/room-blocks', [CalendarController::class, 'storeBlock']);
    Route::delete('/room-blocks/{id}', [CalendarController::class, 'deleteBlock']);

    // BOOKINGS (ADMIN)
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{id}/paid', [BookingController::class, 'markPaid']);

    // CHECK-IN
    Route::post('/checkin', [CheckinController::class, 'checkin']);

    // RATINGS MODERATION
    Route::get('/ratings', [AdminRatingController::class, 'index']);
    Route::post('/ratings/{id}/toggle', [AdminRatingController::class, 'toggleVisibility']);


    Route::get('/dashboard', [ReportController::class, 'dashboard']);

    // REPORTS
    Route::prefix('reports')->group(function () {
        Route::get('/weekly-chart', [ReportController::class, 'weeklyChart']);
        Route::get('/monthly-chart', [ReportController::class, 'monthlyChart']);
        Route::get('/yearly-chart', [ReportController::class, 'yearlyChart']);
        Route::get('/top-rooms', [ReportController::class, 'topRooms']);
    });

});
