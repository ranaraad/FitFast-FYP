<?php
namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TopbarStatsComposer
{
    public function compose(View $view)
    {
        if (!Auth::check()) {
            $view->with('topbarStats', $this->getEmptyStats());
            return;
        }

        // Cache for 60 seconds - crucial!
        $stats = Cache::remember('topbar_stats', 60, function() {
            return $this->calculateStats();
        });

        $view->with('topbarStats', $stats);
    }

    private function calculateStats()
    {
        // Use raw queries for better performance
        return [
            'pending_support' => DB::table('chat_support')
                ->where('status', 'pending')
                ->count(),

            'active_users' => DB::table('users')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('orders')
                        ->whereColumn('orders.user_id', 'users.id');
                })
                ->count(),

            'today_revenue' => DB::table('payments')
                ->whereDate('created_at', today())
                ->sum('amount'),

            'active_stores' => DB::table('stores')
                ->where('status', 'active')
                ->count(),

            'stores_need_attention' => DB::table('stores')
                ->where('status', 'active')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('items')
                        ->whereColumn('items.store_id', 'stores.id')
                        ->where('stock_quantity', '<', 5);
                })
                ->count(),
        ];
    }

    private function getEmptyStats()
    {
        return [
            'pending_support' => 0,
            'active_users' => 0,
            'today_revenue' => 0,
            'active_stores' => 0,
            'stores_need_attention' => 0,
        ];
    }
}
