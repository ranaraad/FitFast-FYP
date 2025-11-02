<?php

namespace App\Http\Controllers\StoreAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Order;
use App\Models\Item;

class StoreController extends Controller
{
    public function index()
    {
        // For now, we'll hardcode a user ID until authentication is set up
        $userId = 1; // Temporary - this should be the logged-in store admin user ID

        $stores = Store::where('user_id', $userId)
            ->with(['user', 'items'])
            ->withCount([
                'items',
                'items as low_stock_items_count' => function($query) {
                    $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
                },
                'items as critical_stock_items_count' => function($query) {
                    $query->where('stock_quantity', '<', 5)->where('stock_quantity', '>', 0);
                },
                'items as out_of_stock_items_count' => function($query) {
                    $query->where('stock_quantity', 0);
                },
                'orders as pending_orders_count' => function($query) {
                    $query->where('status', 'pending');
                },
                'orders as processing_orders_count' => function($query) {
                    $query->where('status', 'processing');
                },
                'orders as completed_orders_count' => function($query) {
                    $query->where('status', 'completed');
                },
                'orders as cancelled_orders_count' => function($query) {
                    $query->where('status', 'cancelled');
                }
            ])
            ->with(['orders' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(5);
            }])
            ->latest()
            ->get();

        // Get summary statistics (matching your CMS pattern but with store admin scope)
        $summary = [
            'total_stores' => $stores->count(),
            'total_items' => $stores->sum('items_count'),
            'total_pending_orders' => $stores->sum('pending_orders_count'),
            'total_low_stock' => $stores->sum('low_stock_items_count'),
            'total_critical_stock' => $stores->sum('critical_stock_items_count'),
            'total_out_of_stock' => $stores->sum('out_of_stock_items_count'),
        ];

        return view('cms.pages.store-admin.stores.index', compact('stores', 'summary'));
    }

    public function show(Store $store)
    {
        // Check if the store belongs to the current store admin
        // For now, we'll skip this check until authentication is implemented
        // $userId = 1; // Temporary
        // if ($store->user_id !== $userId) {
        //     abort(403, 'Unauthorized access to this store.');
        // }

        // Load store with relationships (matching your CMS pattern but with store-specific data)
        $store->load(['user', 'items.category', 'orders.user']);

        // Get additional data using separate queries instead of dynamic relationships
        $lowStockItems = $store->items()
            ->where('stock_quantity', '<', 10)
            ->where('stock_quantity', '>', 0)
            ->get();

        $outOfStockItems = $store->items()
            ->where('stock_quantity', 0)
            ->get();

        $recentOrders = $store->orders()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Get store statistics specific to store admin view
        $storeStats = [
            'total_revenue' => $store->orders()->where('status', 'completed')->sum('total_amount'),
            'avg_order_value' => $store->orders()->where('status', 'completed')->avg('total_amount'),
            'total_customers' => $store->orders()->distinct('user_id')->count('user_id'),
            'pending_orders_count' => $store->orders()->where('status', 'pending')->count(),
            'processing_orders_count' => $store->orders()->where('status', 'processing')->count(),
            'completed_orders_count' => $store->orders()->where('status', 'completed')->count(),
            'low_stock_items_count' => $lowStockItems->count(),
            'out_of_stock_items_count' => $outOfStockItems->count(),
        ];

        return view('cms.pages.store-admin.stores.show', compact(
            'store',
            'storeStats',
            'lowStockItems',
            'outOfStockItems',
            'recentOrders'
        ));
    }
}
