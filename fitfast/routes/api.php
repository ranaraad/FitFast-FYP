<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\UserController; 
use App\Http\Controllers\Client\StoreController;
use App\Http\Controllers\API\SupportChatController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
   Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{store}', [StoreController::class, 'show']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [UserController::class, 'show']);

    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/password', [UserController::class, 'updatePassword']);

        Route::get('/chat-support', [SupportChatController::class, 'index']);
    Route::post('/chat-support', [SupportChatController::class, 'store']);
    Route::get('/chat-support/{chatSupport}', [SupportChatController::class, 'show']);
    Route::post('/chat-support/{chatSupport}/reply', [SupportChatController::class, 'reply']);

});
