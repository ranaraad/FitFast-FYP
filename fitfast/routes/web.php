<?php

use App\Http\Controllers\CMS\UserController as CMSUserController;
use App\Http\Controllers\CMS\DashboardController;
use \App\Http\Controllers\CMS\ChatSupportController;
use App\Http\Controllers\CMS\FAQController;
use App\Http\Controllers\CMS\TipController;
use Illuminate\Support\Facades\Route;

// Public routes (if any)
Route::get('/', function () {
    return view('welcome');
});


// CMS Routes (Admin Panel)
Route::prefix('cms')->name('cms.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', CMSUserController::class)->names([
        'index' => 'users.index',
        'create' => 'users.create',
        'store' => 'users.store',
        'show' => 'users.show',
        'edit' => 'users.edit',
        'update' => 'users.update',
        'destroy' => 'users.destroy'
    ]);
    Route::resource('chat-support', ChatSupportController::class);
    Route::post('chat-support/{chatSupport}/take', [ChatSupportController::class, 'takeChat'])->name('chat-support.take');
    Route::post('chat-support/{chatSupport}/resolve', [ChatSupportController::class, 'resolve'])->name('chat-support.resolve');

    // FAQs
    Route::resource('faqs', FAQController::class);

    // Tips
    Route::resource('tips', TipController::class);
});
