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

        // Recent activity
        $recentActivity = $this->getRecentActivity();

        return view('cms.pages.dashboard.index', compact(
            'stats',
            'revenueStats',
            'orderStats',
            'userStats',
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

    private function getRevenueTrend()
    {
        $currentMonth = Payment::whereMonth('created_at', Carbon::now()->month)->sum('amount');
        $lastMonth = Payment::whereMonth('created_at', Carbon::now()->subMonth()->month)->sum('amount');

        if ($lastMonth == 0) return 100; // 100% increase if no previous data

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
        ];
    }
}
