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

Route::prefix('user')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'generateOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/rooms', [UserController::class, 'indexRooms']);
    Route::get('/rooms/{$room}', [UserController::class, 'showRoom']);
    Route::get('/rooms/{$room}/calendar', [UserController::class, 'calendarAvailability']);

    Route::post('/rooms/{$room}/book', [UserController::class, 'bookRoom']);
    Route::get('/my-bookings', [UserController::class, 'myBooking']);

    Route::post('/bookings/{$booking}/review', [UserController::class, 'submitReview']);
    Route::get('my-reviews', [UserController::class, 'myReviews']);

    Route::post('/chat/send', [AuthController::class, 'sendChat']);
    Route::get('/chat', [UserController::class, 'myChat']);
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'loginAdmin']);
    Route::post('/logout', [AuthController::class, 'logout']);
});





