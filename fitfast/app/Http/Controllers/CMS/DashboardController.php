<?php

namespace App\Http\Controllers\CMS;

use App\Models\User;
use App\Models\Order;
use App\Models\Item;
use App\Models\Store;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\ChatSupport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic counts
        $stats = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_items' => Item::count(),
            'total_stores' => Store::count(),
            'active_carts' => Cart::withItems()->count(),
            'pending_support' => ChatSupport::where('status', 'pending')->count(),
        ];

        // Revenue analytics
        $revenueStats = $this->getRevenueStats();

        // Order analytics
        $orderStats = $this->getOrderStats();

        // User analytics
        $userStats = $this->getUserStats();

        // Store performance metrics
        $storeStats = $this->getStorePerformanceStats();

        // Recent activity
        $recentActivity = $this->getRecentActivity();

        return view('cms.pages.dashboard.index', compact(
            'stats',
            'revenueStats',
            'orderStats',
            'userStats',
            'storeStats',
            'recentActivity'
        ));
    }

    private function getRevenueStats()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        return [
            'today' => Payment::whereDate('created_at', $today)->sum('amount'),
            'this_week' => Payment::where('created_at', '>=', $weekStart)->sum('amount'),
            'this_month' => Payment::where('created_at', '>=', $monthStart)->sum('amount'),
            'total_revenue' => Payment::sum('amount'),
            'average_order_value' => Payment::avg('amount') ?: 0,
            'revenue_trend' => $this->getRevenueTrend(),
        ];
    }

    private function getOrderStats()
    {
        return [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'conversion_rate' => $this->getConversionRate(),
        ];
    }

    private function getUserStats()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        return [
            'new_today' => User::whereDate('created_at', $today)->count(),
            'new_this_week' => User::where('created_at', '>=', $weekStart)->count(),
            'new_this_month' => User::where('created_at', '>=', $monthStart)->count(),
            'active_users' => User::has('orders')->count(),
        ];
    }

    private function getStorePerformanceStats()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        // Calculate total revenue from stores
        $totalStoreRevenue = Payment::whereHas('order.store')->sum('amount');

        return [
            // Overview metrics
            'total_stores' => Store::count(),
            'active_stores' => Store::where('status', 'active')->count(),
            'inactive_stores' => Store::where('status', 'inactive')->count(),
            'stores_with_orders' => Store::has('orders')->count(),

            // Revenue metrics
            'total_revenue' => $totalStoreRevenue,
            'revenue_today' => Payment::whereDate('created_at', $today)
                ->whereHas('order.store')
                ->sum('amount'),
            'revenue_this_week' => Payment::where('created_at', '>=', $weekStart)
                ->whereHas('order.store')
                ->sum('amount'),
            'revenue_this_month' => Payment::where('created_at', '>=', $monthStart)
                ->whereHas('order.store')
                ->sum('amount'),

            // Top performing stores
            'top_stores_by_revenue' => Store::withSum(['orders' => function($query) {
                $query->whereHas('payment', function($q) {
                    $q->where('status', 'completed');
                });
            }], 'total_amount')
            ->orderBy('orders_sum_total_amount', 'desc')
            ->take(5)
            ->get()
            ->map(function($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'revenue' => $store->orders_sum_total_amount ?? 0,
                    'order_count' => $store->orders()->count(),
                    'status' => $store->status,
                ];
            }),

            // Store order distribution
            'stores_with_most_orders' => Store::withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->take(5)
                ->get()
                ->map(function($store) {
                    return [
                        'id' => $store->id,
                        'name' => $store->name,
                        'order_count' => $store->orders_count,
                        'status' => $store->status,
                    ];
                }),

            // Inventory metrics
            'total_items_across_stores' => Item::count(),
            'average_items_per_store' => Store::count() > 0
                ? round(Item::count() / Store::count(), 2)
                : 0,
            'stores_with_most_items' => Store::withCount('items')
                ->orderBy('items_count', 'desc')
                ->take(5)
                ->get(),
            'low_stock_stores' => Store::whereHas('items', function($query) {
                $query->where('stock_quantity', '<', 10);
            })->count(),

            // Revenue growth
            'revenue_growth' => $this->getStoreRevenueGrowth(),
        ];
    }

    private function getStoreRevenueGrowth()
    {
        $currentMonthRevenue = Payment::whereHas('order.store')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');

        $lastMonthRevenue = Payment::whereHas('order.store')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('amount');

        if ($lastMonthRevenue == 0) {
            $growthRate = $currentMonthRevenue > 0 ? 100 : 0;
        } else {
            $growthRate = (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        }

        return [
            'current_month' => $currentMonthRevenue,
            'last_month' => $lastMonthRevenue,
            'growth_rate' => round($growthRate, 2),
            'trend_direction' => $growthRate >= 0 ? 'up' : 'down',
        ];
    }

    private function getRevenueTrend()
    {
        $currentMonth = Payment::whereMonth('created_at', Carbon::now()->month)->sum('amount');
        $lastMonth = Payment::whereMonth('created_at', Carbon::now()->subMonth()->month)->sum('amount');

        if ($lastMonth == 0) return 100;
        return (($currentMonth - $lastMonth) / $lastMonth) * 100;
    }

    private function getConversionRate()
    {
        $totalUsers = User::count();
        $usersWithOrders = User::has('orders')->count();
        if ($totalUsers == 0) return 0;
        return ($usersWithOrders / $totalUsers) * 100;
    }

    private function getRecentActivity()
    {
        return [
            'recent_orders' => Order::with(['user', 'store'])
                ->latest()
                ->take(5)
                ->get(),
            'recent_users' => User::latest()
                ->take(5)
                ->get(),
            'pending_support' => ChatSupport::with(['user', 'admin'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'recent_stores' => Store::withCount(['items', 'orders'])
                ->latest()
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Get detailed store performance for a specific store
     */
    public function storePerformance($storeId)
    {
        $store = Store::withCount(['items', 'orders'])->findOrFail($storeId);

        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $storeStats = [
            'store' => $store,
            'total_revenue' => Payment::whereHas('order', function($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })->sum('amount'),
            'revenue_today' => Payment::whereHas('order', function($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })->whereDate('created_at', $today)->sum('amount'),
            'revenue_this_week' => Payment::whereHas('order', function($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })->where('created_at', '>=', $weekStart)->sum('amount'),
            'revenue_this_month' => Payment::whereHas('order', function($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })->where('created_at', '>=', $monthStart)->sum('amount'),
            'order_stats' => [
                'pending' => $store->orders()->where('status', 'pending')->count(),
                'processing' => $store->orders()->where('status', 'processing')->count(),
                'shipped' => $store->orders()->where('status', 'shipped')->count(),
                'delivered' => $store->orders()->where('status', 'delivered')->count(),
                'cancelled' => $store->orders()->where('status', 'cancelled')->count(),
            ],
            'inventory_stats' => [
                'total_items' => $store->items_count,
                'low_stock_items' => $store->items()->where('stock_quantity', '<', 10)->count(),
                'out_of_stock_items' => $store->items()->where('stock_quantity', 0)->count(),
            ],
            'recent_orders' => $store->orders()
                ->with(['user', 'payment'])
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('cms.pages.stores.performance', compact('storeStats'));
    }
}
