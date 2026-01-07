<?php

use App\Http\Controllers\AdminRoomController;
use App\Http\Controllers\AuthController;
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


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/admin/rooms', [AdminRoomController::class, 'store']);
    Route::post('/admin/rooms/{id}', [AdminRoomController::class, 'update']);
    Route::post('/admin/rooms/{id}', [AdminRoomController::class, 'destroy']);

    Route::post('/admin/rooms/{roomId}/images', [RoomImageController::class, 'store']);
    Route::post('/admin/room-images/{id}/cover', [RoomImageController::class, 'setCover']);
    Route::post('/admin/room-images/{id}', [RoomImageController::class, 'destroy']);

    Route::post('/admin/room-blocks', [RoomBlockController::class, 'store']);

});