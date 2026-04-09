<?php

use App\Http\Controllers\Api\MakeController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\PowerModController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ================================
// API ROUTES - MUST BE FIRST
// ================================
Route::prefix('api')->group(function () {
    // Car Data (Public)
    Route::get('makes', [MakeController::class, 'index']);
    Route::get('models', [CarController::class, 'index']);
    Route::get('cars', [CarController::class, 'index']);
    Route::get('cars/{id}', [CarController::class, 'show']);
    Route::get('powermods', [CarController::class, 'index']);
    Route::get('cars/{carId}/engines/{engineId}/parts', [PowerModController::class, 'getAvailableParts']);

    // PROTECTED API ROUTES (Require Auth)
    Route::middleware('auth:web')->group(function () {
        // Dashboard & Profile
        Route::get('dashboard', [UserDashboardController::class, 'show']);
        Route::get('users/{id}/profile', [UserDashboardController::class, 'showProfile']);

        // Leaderboards
        Route::get('leaderboards', [LeaderboardController::class, 'index']);

        // Projects (Builds)
        Route::get('projects', [ProjectController::class, 'index']);
        Route::post('projects', [ProjectController::class, 'store']);
        Route::get('projects/{id}', [ProjectController::class, 'show']);
        Route::put('projects/{id}', [ProjectController::class, 'update']);
        Route::delete('projects/{id}', [ProjectController::class, 'destroy']);

        // Avatar Upload/Delete
        Route::post('avatar/upload', [AvatarController::class, 'upload']);
        Route::delete('avatar/delete', [AvatarController::class, 'delete']);
    });
});

// ================================
// AUTH ROUTES
// ================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:web');

// USER ROUTES
Route::middleware('auth:web')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// ================================
// CATCH-ALL FOR VUE ROUTER - MUST BE LAST
// ================================
Route::fallback(function () {
    return view('welcome');
});
