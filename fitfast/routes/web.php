<?php

use App\Http\Controllers\CMS\UserController as CMSUserController;
use App\Http\Controllers\CMS\DashboardController;
use \App\Http\Controllers\CMS\ChatSupportController;
use App\Http\Controllers\CMS\FAQController;
use App\Http\Controllers\CMS\TipController;
use App\Http\Controllers\CMS\ItemController;
use App\Http\Controllers\CMS\StoreController;
use App\Http\Controllers\CMS\OrderController;
use App\Http\Controllers\CMS\ReviewController;
use App\Http\Controllers\CMS\CartController;
use App\Http\Controllers\CMS\DeliveryController;
use App\Http\Controllers\CMS\PaymentController;
use App\Http\Controllers\CMS\PaymentMethodController;
use App\Http\Controllers\CMS\RoleController;
use App\Http\Controllers\CMS\OrderItemController;
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
    Route::resource('roles', RoleController::class);

    // Items
    Route::resource('items', ItemController::class);

    // Stores
    Route::resource('stores', StoreController::class);
// Orders
Route::resource('orders', OrderController::class);
Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::get('orders/cart/{cart}/items', [OrderController::class, 'getCartItems'])->name('orders.cart-items');    // Reviews
    Route::resource('reviews', ReviewController::class)->only([
        'index', 'show', 'destroy'
    ]);
    Route::get('reviews/item/{item}', [ReviewController::class, 'itemReviews'])->name('reviews.item');
    Route::get('reviews/user/{user}', [ReviewController::class, 'userReviews'])->name('reviews.user');

    // Carts 
    Route::resource('carts', CartController::class); // Now includes all methods
    Route::post('carts/{cart}/clear', [CartController::class, 'clearCart'])->name('carts.clear');
Route::get('carts/user/{user}', [CartController::class, 'getUserCarts'])->name('carts.user-carts');  

// Deliveries
    Route::resource('deliveries', DeliveryController::class);
    Route::post('deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('deliveries.update-status');
    Route::post('deliveries/{delivery}/shipped', [DeliveryController::class, 'markAsShipped'])->name('deliveries.mark-shipped');
    Route::post('deliveries/{delivery}/delivered', [DeliveryController::class, 'markAsDelivered'])->name('deliveries.mark-delivered');
    Route::get('deliveries/status/{status}', [DeliveryController::class, 'byStatus'])->name('deliveries.by-status');

    // Payments
    Route::resource('payments', PaymentController::class);
    Route::post('payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.update-status');
    Route::post('payments/{payment}/complete', [PaymentController::class, 'markAsCompleted'])->name('payments.mark-completed');
    Route::post('payments/{payment}/fail', [PaymentController::class, 'markAsFailed'])->name('payments.mark-failed');
    Route::post('payments/{payment}/refund', [PaymentController::class, 'markAsRefunded'])->name('payments.mark-refunded');
    Route::get('payments/status/{status}', [PaymentController::class, 'byStatus'])->name('payments.by-status');

    // Payment Methods
    Route::resource('payment-methods', PaymentMethodController::class);
    Route::post('payment-methods/{paymentMethod}/default', [PaymentMethodController::class, 'setAsDefault'])->name('payment-methods.set-default');
    Route::get('payment-methods/user/{user}', [PaymentMethodController::class, 'byUser'])->name('payment-methods.by-user');
    Route::get('payment-methods/type/{type}', [PaymentMethodController::class, 'byType'])->name('payment-methods.by-type');

    // FAQs
    Route::resource('faqs', FAQController::class);

    // Tips
    Route::resource('tips', TipController::class);
});
