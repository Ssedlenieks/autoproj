<?php

use App\Http\Controllers\Api\MakeController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\AvatarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API Routes
Route::prefix('api')->group(function () {
    Route::get('makes', [MakeController::class, 'index']);
    Route::get('models', [CarController::class, 'index']);
    Route::get('cars', [CarController::class, 'index']);
    Route::get('cars/{id}', [CarController::class, 'show']);
});

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Protected Auth Routes
Route::middleware('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/avatar/upload', [AvatarController::class, 'upload']);
});

// Catch-all route for Vue Router - MUST BE LAST
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
