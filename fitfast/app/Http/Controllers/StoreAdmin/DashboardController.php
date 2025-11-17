<?php

namespace App\Http\Controllers\StoreAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Order;
use App\Models\Item;
use App\Models\Delivery;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Get stores managed by this user
        $stores = Store::where('user_id', $userId)
            ->withCount([
                'items',
                'items as low_stock_items_count' => function($query) {
                    $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
                },
                'items as out_of_stock_items_count' => function($query) {
                    $query->where('stock_quantity', 0);
                },
                'orders as pending_orders_count' => function($query) {
                    $query->where('status', 'pending');
                },
                'orders as completed_orders_count' => function($query) {
                    $query->where('status', 'completed');
                }
            ])
            ->with(['items' => function($query) {
                $query->orderBy('stock_quantity', 'asc')->limit(5);
            }])
            ->get();

        // Get overall stats for the topbar
        $topbarStats = [
            'total_stores' => $stores->count(),
            'total_items' => $stores->sum('items_count'),
            'pending_orders' => $stores->sum('pending_orders_count'),
            'low_stock_items' => $stores->sum('low_stock_items_count'),
            'out_of_stock_items' => $stores->sum('out_of_stock_items_count'),
            'today_revenue' => Order::whereIn('store_id', $stores->pluck('id'))
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total_amount') ?? 0,
        ];

        return view('cms.pages.store-admin.dashboard', compact('stores', 'topbarStats'));
    }
}
