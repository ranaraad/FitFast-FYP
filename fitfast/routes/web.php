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
use App\Http\Controllers\CMS\Auth\LoginController;
use App\Http\Controllers\CMS\Auth\RegisterController;
use App\Http\Controllers\CMS\Auth\EmailVerificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreAdmin\DashboardController as StoreAdminDashboardController;
use App\Http\Controllers\StoreAdmin\StoreController as StoreAdminStoreController;
use App\Http\Controllers\StoreAdmin\ItemController as StoreAdminItemController;
use App\Http\Controllers\StoreAdmin\OrderController as StoreAdminOrderController;
use App\Http\Controllers\StoreAdmin\DeliveryController as StoreAdminDeliveryController;

// Redirect root to CMS login
Route::get('/', function () {
    return redirect()->route('cms.login');
});

// Public CMS Auth Routes
Route::prefix('cms')->name('cms.')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
});

// Email Verification Routes
Route::get('/cms/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/cms/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/cms/email/verification-notification', [EmailVerificationController::class, 'send'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Protected CMS Routes (Super Admin only - requires auth, verified email, and admin role)
Route::prefix('cms')->name('cms.')->middleware(['auth', 'verified', 'cms.access'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
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

    // Item Images Management
    Route::post('items/{item}/images', [ItemController::class, 'addImages'])->name('items.images.store');
    Route::post('items/{item}/images/{image}/set-primary', [ItemController::class, 'setPrimaryImage'])->name('items.images.set-primary');
    Route::post('items/{item}/images/reorder', [ItemController::class, 'reorderImages'])->name('items.images.reorder');
    Route::delete('items/{item}/images/{image}', [ItemController::class, 'deleteImage'])->name('items.images.destroy');

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
    Route::resource('carts', CartController::class);
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

// Protected Store Admin Routes (requires auth, verified email, and store admin role)
Route::prefix('store-admin')->name('store-admin.')->middleware(['auth', 'verified', 'storeadmin.access'])->group(function () {
    // Add logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [StoreAdminDashboardController::class, 'index'])->name('dashboard');

    // Store Admin specific exports
    Route::get('/items/export', [StoreAdminItemController::class, 'export'])->name('items.export');
    Route::get('/items/export-low-stock', [StoreAdminItemController::class, 'exportLowStock'])->name('items.export-low-stock');
    Route::get('/orders/export', [StoreAdminOrderController::class, 'export'])->name('orders.export');
    Route::get('/orders/export-advanced', [StoreAdminOrderController::class, 'exportAdvanced'])->name('orders.export-advanced');

    // Stores - Only show stores managed by the current store admin
    Route::get('/stores', [StoreAdminStoreController::class, 'index'])->name('stores.index');
    Route::get('/stores/{store}', [StoreAdminStoreController::class, 'show'])->name('stores.show');

    // Items - Resource routes with custom methods
    Route::resource('items', StoreAdminItemController::class);

    // Store Admin Item Images Management
    Route::post('items/{item}/images', [StoreAdminItemController::class, 'addImages'])->name('items.images.store');
    Route::post('items/{item}/images/{image}/set-primary', [StoreAdminItemController::class, 'setPrimaryImage'])->name('items.images.set-primary');
    Route::post('items/{item}/images/reorder', [StoreAdminItemController::class, 'reorderImages'])->name('items.images.reorder');
    Route::delete('items/{item}/images/{image}', [StoreAdminItemController::class, 'deleteImage'])->name('items.images.destroy');

    // Orders - Resource routes with custom status update
    Route::resource('orders', StoreAdminOrderController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::post('orders/{order}/status', [StoreAdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Deliveries - Resource routes with custom methods
    Route::get('deliveries/search', [StoreAdminDeliveryController::class, 'search'])->name('deliveries.search');
    Route::resource('deliveries', StoreAdminDeliveryController::class)->only(['index', 'destroy']);
    Route::post('deliveries/{delivery}/update-status', [StoreAdminDeliveryController::class, 'updateStatus'])->name('deliveries.update-status');
    Route::post('deliveries/{delivery}/add-tracking', [StoreAdminDeliveryController::class, 'addTracking'])->name('deliveries.add-tracking');
    Route::post('deliveries/{delivery}/update-tracking', [StoreAdminDeliveryController::class, 'updateTracking'])->name('deliveries.update-tracking');
    Route::post('deliveries/{delivery}/mark-delivered', [StoreAdminDeliveryController::class, 'markAsDelivered'])->name('deliveries.mark-delivered');
});
