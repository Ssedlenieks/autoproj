<?php

use App\Http\Controllers\Api\MakeController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\PowerModController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DailyChallengeController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\EditorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ================================
// API ROUTES
// ================================
Route::prefix('api')->group(function () {

    // Car Data (Public)
    Route::get('makes', [MakeController::class, 'index']);
    Route::get('models', [CarController::class, 'index']);
    Route::get('cars', [CarController::class, 'index']);
    Route::get('cars/{id}', [CarController::class, 'show']);
    Route::get('powermods', [PowerModController::class, 'index']);
    Route::get('engines', [EditorController::class, 'getEngines']);
    Route::get('cars/{carId}/engines/{engineId}/parts', [PowerModController::class, 'getAvailableParts']);

    // PROTECTED ROUTES
    Route::middleware('auth:web')->group(function () {

        // Dashboard & Profile
        Route::get('dashboard', [UserDashboardController::class, 'show']);
        Route::get('users/{id}/profile', [UserDashboardController::class, 'showProfile']);

        // Leaderboards
        Route::get('leaderboards', [LeaderboardController::class, 'index']);

        // Public Projects (citu lietotāju publiskie projekti)
        Route::get('public-projects', [ProjectController::class, 'publicIndex']);

        // Projects
        Route::get('projects', [ProjectController::class, 'index']);
        Route::post('projects', [ProjectController::class, 'store']);
        Route::get('projects/{id}', [ProjectController::class, 'show']);
        Route::put('projects/{id}', [ProjectController::class, 'update']);
        Route::delete('projects/{id}', [ProjectController::class, 'destroy']);
        Route::patch('projects/{id}/visibility', [ProjectController::class, 'toggleVisibility']);

        // Avatar
        Route::post('avatar/upload', [AvatarController::class, 'upload']);
        Route::delete('avatar/delete', [AvatarController::class, 'delete']);

        // Daily Challenges
        Route::get('challenges', [DailyChallengeController::class, 'index']);

        // Editor Routes (Editor + Admin)
        Route::middleware('editor')->prefix('editor')->group(function () {
            // Makes
            Route::post('makes',              [EditorController::class, 'storeMake']);
            Route::put('makes/{id}',          [EditorController::class, 'updateMake']);
            Route::delete('makes/{id}',       [EditorController::class, 'deleteMake']);

            // Models
            Route::post('models',             [EditorController::class, 'storeModel']);
            Route::put('models/{id}',         [EditorController::class, 'updateModel']);
            Route::delete('models/{id}',      [EditorController::class, 'deleteModel']);

            // Cars
            Route::post('cars',               [EditorController::class, 'storeCar']);
            Route::put('cars/{id}',           [EditorController::class, 'updateCar']);
            Route::delete('cars/{id}',        [EditorController::class, 'deleteCar']);

            // Engines
            Route::post('engines',            [EditorController::class, 'storeEngine']);
            Route::put('engines/{id}',        [EditorController::class, 'updateEngine']);
            Route::delete('engines/{id}',     [EditorController::class, 'deleteEngine']);

            // Car-Engine links
            Route::post('car-engines',        [EditorController::class, 'storeCarEngine']);
            Route::put('car-engines/{id}',    [EditorController::class, 'updateCarEngine']);
            Route::delete('car-engines/{id}', [EditorController::class, 'deleteCarEngine']);

            // Power Mods
            Route::post('power-mods',              [EditorController::class, 'storePowerMod']);
            Route::put('power-mods/{id}',          [EditorController::class, 'updatePowerMod']);
            Route::delete('power-mods/{id}',       [EditorController::class, 'deletePowerMod']);

            // Power Mod Variants
            Route::post('power-mod-variants',         [EditorController::class, 'storePowerModVariant']);
            Route::put('power-mod-variants/{id}',     [EditorController::class, 'updatePowerModVariant']);
            Route::delete('power-mod-variants/{id}',  [EditorController::class, 'deletePowerModVariant']);
        });

        // Admin Routes
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('overview',             [AdminController::class, 'overview']);
            Route::get('users',                [AdminController::class, 'users']);
            Route::patch('users/{id}/role',    [AdminController::class, 'updateRole']);
            Route::delete('users/{id}',        [AdminController::class, 'deleteUser']);
            Route::get('achievements',         [AdminController::class, 'achievements']);
            Route::post('achievements',        [AdminController::class, 'storeAchievement']);
            Route::put('achievements/{id}',    [AdminController::class, 'updateAchievement']);
            Route::delete('achievements/{id}', [AdminController::class, 'deleteAchievement']);
            Route::get('challenges',           [AdminController::class, 'challenges']);
            Route::post('challenges',          [AdminController::class, 'storeChallenge']);
            Route::put('challenges/{id}',      [AdminController::class, 'updateChallenge']);
            Route::delete('challenges/{id}',   [AdminController::class, 'deleteChallenge']);
            Route::get('projects',             [AdminController::class, 'projects']);
            Route::delete('projects/{id}',     [AdminController::class, 'deleteProject']);
        });
    });
});

// ================================
// AUTH ROUTES
// ================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:web');

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