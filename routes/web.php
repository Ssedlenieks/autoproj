<?php
use App\Http\Controllers\Api\MakeController;
use App\Http\Controllers\Api\CarController;


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('api')->group(function () {
    Route::get('makes', [MakeController::class, 'index']);
    Route::get('models', [CarController::class, 'index']);
    Route::get('cars', [CarController::class, 'index']);
    Route::get('cars/{id}', [CarController::class, 'show']);
});

