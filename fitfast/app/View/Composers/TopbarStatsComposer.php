<?php

namespace App\View\Composers;

use App\Models\ChatSupport;
use App\Models\User;
use App\Models\Payment;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class TopbarStatsComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            try {
                $stats = [
                    'pending_support' => ChatSupport::where('status', 'pending')->count(),
                    'active_users' => User::has('orders')->count(),
                    'today_revenue' => Payment::whereDate('created_at', today())->sum('amount'),
                    'active_stores' => Store::where('status', 'active')->count(),
                    'stores_need_attention' => Store::whereHas('items', function($query) {
                        $query->where('stock_quantity', '<', 5);
                    })->count(),
                ];

                // Log for debugging
                Log::info('Topbar Stats:', $stats);

                $view->with('topbarStats', $stats);
            } catch (\Exception $e) {
                Log::error('TopbarStatsComposer Error: ' . $e->getMessage());
                $view->with('topbarStats', [
                    'pending_support' => 0,
                    'active_users' => 0,
                    'today_revenue' => 0,
                    'active_stores' => 0,
                    'stores_need_attention' => 0,
                ]);
            }
        } else {
            $view->with('topbarStats', [
                'pending_support' => 0,
                'active_users' => 0,
                'today_revenue' => 0,
                'active_stores' => 0,
                'stores_need_attention' => 0,
            ]);
        }
    }
}
