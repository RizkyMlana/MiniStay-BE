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

    // USER LOGIN
    Route::post('/send-otp', [AuthController::class, 'generateOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    // ADMIN LOGIN
    Route::post('/admin/login', [AuthController::class, 'loginAdmin']);

    // UNIVERSAL LOGOUT (user/admin)
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:user,admin');
});



Route::prefix('user')->middleware('auth:user')->group(function () {

    // ROOMS
    Route::get('/rooms', [UserController::class, 'indexRooms']);
    Route::get('/rooms/{room}', [UserController::class, 'showRoom']);
    Route::get('/rooms/{room}/calendar', [UserController::class, 'calendarAvailability']);

    // BOOKING
    Route::post('/rooms/{room}/book', [UserController::class, 'bookRoom']);
    Route::get('/my-bookings', [UserController::class, 'myBooking']);

    // REVIEW
    Route::post('/bookings/{booking}/review', [UserController::class, 'submitReview']);
    Route::get('/my-reviews', [UserController::class, 'myReviews']);

    // PAYMENT
    Route::post('/payments', [PaymentController::class, 'storePayment']);
    Route::get('/my-payments', [PaymentController::class, 'myPayments']);

    // CHAT
    Route::post('/chat/send', [ChatController::class, 'sendMessageAsUser']);
    Route::get('/chat', [ChatController::class, 'myChats']);
});




Route::prefix('admin')->middleware('auth:admin')->group(function () {

    // BOOKING
    Route::get('/bookings', [AdminController::class, 'listBooking']);
    Route::get('/bookings/{id}', [AdminController::class, 'showBooking']);
    Route::patch('/bookings/{id}/status', [AdminController::class, 'updateBookingStatus']);
    Route::patch('/bookings/{id}/cancel', [AdminController::class, 'cancelBooking']);

    // PAYMENT
    Route::patch('/payments/{id}/confirm', [AdminController::class, 'confirmPayment']);

    // ROOMS
    Route::post('/rooms', [RoomController::class, 'createRoom']);
    Route::put('/rooms/{id}', [RoomController::class, 'updateRoom']);
    Route::post('/rooms/{id}/upload-photo', [RoomController::class, 'uploadPhoto']);
    Route::patch('/rooms/{id}/availability', [RoomController::class, 'updateAvailability']);
    Route::get('/rooms/{id}/calendar', [RoomController::class, 'calendarAdmin']);

    // QR SCAN
    Route::post('/scan', [BookingScanController::class, 'scanQr']);
    Route::get('/scans', [BookingScanController::class, 'listScans']);

    // REVIEW MODERATION
    Route::patch('/reviews/{id}/moderate', [ReviewController::class, 'moderateReview']);

    // REPORT
    Route::get('/reports/daily', [ReportController::class, 'dailyReport']);
    Route::get('/reports/weekly', [ReportController::class, 'weeklyReport']);
    Route::get('/reports/monthly', [ReportController::class, 'monthlyReport']);
    Route::get('/reports/rooms/popular', [ReportController::class, 'mostBookedRooms']);

    Route::get('/chat', [ChatController::class, 'listActiveChats']);
    Route::get('/chat/{user_id}', [ChatController::class, 'getMessagesWithUser']);
    Route::post('/chat/{user_id}/send', [ChatController::class, 'sendMessageAsAdmin']);
    Route::patch('/chat/{user_id}/seen', [ChatController::class, 'markMessagesAsSeen']);
});
