<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BookingScanController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;



Route::prefix('auth')->group(function () {

    Route::post('/send-otp', [AuthController::class, 'generateOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/admin/login', [AuthController::class, 'loginAdmin']);
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:user,admin');
});



Route::prefix('user')->middleware('auth:user')->group(function () {

    // Rooms
    Route::get('/rooms', [UserController::class, 'indexRooms']);
    Route::get('/rooms/{room}', [UserController::class, 'showRoom']);
    Route::get('/rooms/{room}/calendar', [UserController::class, 'calendarAvailability']);

    // Booking
    Route::post('/rooms/{room}/book', [UserController::class, 'bookRoom']);
    Route::get('/my-bookings', [UserController::class, 'myBooking']);

    // Review
    Route::post('/bookings/{booking}/review', [UserController::class, 'submitReview']);
    Route::get('/my-reviews', [UserController::class, 'myReviews']);

    // Payment
    Route::post('/payments', [PaymentController::class, 'storePayment']);
    Route::get('/my-payments', [PaymentController::class, 'myPayments']);

    // Chat
    Route::post('/chat/send', [ChatController::class, 'sendMessageAsUser']);
    Route::get('/chat', [ChatController::class, 'myChats']);
});




Route::prefix('admin')->middleware('auth:admin')->group(function () {

    // Booking
    Route::get('/bookings', [AdminController::class, 'listBooking']);
    Route::get('/bookings/{id}', [AdminController::class, 'showBooking']);
    Route::patch('/bookings/{id}/status', [AdminController::class, 'updateBookingStatus']);
    Route::patch('/bookings/{id}/cancel', [AdminController::class, 'cancelBooking']);
    // Payment
    Route::patch('/payments/{id}/confirm', [AdminController::class, 'confirmPayment']);
    // Rooms
    Route::post('/rooms', [RoomController::class, 'createRoom']);
    Route::put('/rooms/{id}', [RoomController::class, 'updateRoom']);
    Route::delete('/rooms/{id}' , [RoomController::class, 'deleteRoom']);
    Route::post('/rooms/{id}/upload-photo', [RoomController::class, 'uploadPhoto']);
    Route::patch('/rooms/{id}/availability', [RoomController::class, 'updateAvailability']);
    Route::get('/rooms/{id}/calendar', [RoomController::class, 'calendarAdmin']);
    // QR Scan
    Route::post('/scan', [BookingScanController::class, 'scanQr']);
    Route::get('/scans', [BookingScanController::class, 'listScans']);
    // Review Moderation
    Route::patch('/reviews/{id}/moderate', [ReviewController::class, 'moderateReview']);
    // Report
    Route::get('/reports/daily', [ReportController::class, 'dailyReport']);
    Route::get('/reports/weekly', [ReportController::class, 'weeklyReport']);
    Route::get('/reports/monthly', [ReportController::class, 'monthlyReport']);
    Route::get('/reports/rooms/popular', [ReportController::class, 'mostBookedRooms']);
    // Chat
    Route::get('/chat', [ChatController::class, 'listActiveChats']);
    Route::get('/chat/{user_id}', [ChatController::class, 'getMessagesWithUser']);
    Route::post('/chat/{user_id}/send', [ChatController::class, 'sendMessageAsAdmin']);
    Route::patch('/chat/{user_id}/seen', [ChatController::class, 'markMessagesAsSeen']);
});
