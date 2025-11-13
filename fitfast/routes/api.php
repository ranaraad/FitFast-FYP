<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\UserController; 
use App\Http\Controllers\Client\StoreController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [UserController::class, 'show']);

    Route::put('/user', [UserController::class, 'update']);

    Route::get('/stores', [StoreController::class, 'index']);
});
