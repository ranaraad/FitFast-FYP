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
use App\Http\Controllers\CMS\ExportController;
use Illuminate\Support\Facades\Route;

// Public routes (if any)
Route::get('/', function () {
    return view('welcome');
});


// CMS Routes (Admin Panel)
Route::prefix('cms')->name('cms.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Exports
    Route::get('users/export', [ExportController::class, 'exportUsers'])->name('users.export');
    Route::get('items/export', [ExportController::class, 'exportItems'])->name('items.export');
    Route::get('items/export-low-stock', [ExportController::class, 'exportLowStockItems'])->name('items.export-low-stock');
    Route::get('stores/export', [ExportController::class, 'exportStores'])->name('stores.export');
    Route::get('stores/export-alerts', [ExportController::class, 'exportStoresWithAlerts'])->name('stores.export-alerts');
    Route::get('payments/export', [ExportController::class, 'exportPayments'])->name('payments.export');
    Route::get('payments/export/{status}', [ExportController::class, 'exportPaymentsByStatus'])->name('payments.export-by-status');
    Route::post('payments/export-by-date', [ExportController::class, 'exportPaymentsByDateRange'])->name('payments.export-by-date');

    // Users
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
    Route::get('orders/cart/{cart}/items', [OrderController::class, 'getCartItems'])->name('orders.cart-items');

    // Reviews
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
    Route::get('deliveries/search', [DeliveryController::class, 'search'])->name('deliveries.search');
    Route::post('deliveries/{delivery}/mark-delivered', [DeliveryController::class, 'markAsDelivered'])->name('deliveries.mark-delivered');
    Route::post('deliveries/{delivery}/add-tracking', [DeliveryController::class, 'addTracking'])->name('deliveries.add-tracking');
    Route::post('deliveries/{delivery}/update-status', [DeliveryController::class, 'updateStatus'])->name('deliveries.update-status');
    Route::post('deliveries/{delivery}/update-tracking', [DeliveryController::class, 'updateTracking'])->name('deliveries.update-tracking');
    Route::resource('deliveries', DeliveryController::class);

    // Payments
    Route::get('payments/search', [PaymentController::class, 'search'])->name('payments.search');
    Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::resource('payments', PaymentController::class)->only(['index', 'show', 'edit', 'update']);

    // Payment Methods
    Route::resource('payment-methods', PaymentMethodController::class);
    Route::post('payment-methods/{paymentMethod}/set-default', [PaymentMethodController::class, 'setDefault'])
        ->name('payment-methods.set-default');

    // FAQs
    Route::resource('faqs', FAQController::class);

    // Tips
    Route::resource('tips', TipController::class);

    // Chat Support
    Route::resource('chat-support', ChatSupportController::class);
    Route::post('chat-support/{chatSupport}/take', [ChatSupportController::class, 'takeChat'])->name('chat-support.take');
    Route::post('chat-support/{chatSupport}/resolve', [ChatSupportController::class, 'resolve'])->name('chat-support.resolve');
    Route::get('chat-support/status/{status}', [ChatSupportController::class, 'byStatus'])->name('chat-support.by-status');


});


