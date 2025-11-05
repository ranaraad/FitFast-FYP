<?php

namespace App\Http\Controllers\CMS;

use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['order.user', 'order.store', 'paymentMethod'])
            ->latest()
            ->get();

        $stats = [
            'total_payments' => Payment::count(),
            'total_revenue' => Payment::completed()->sum('amount'),
            'pending_payments' => Payment::pending()->count(),
            'failed_payments' => Payment::failed()->count(),
        ];

        return view('cms.pages.payments.index', compact('payments', 'stats'));
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'order.user',
            'order.store',
            'order.orderItems.item',
            'order.delivery',
            'paymentMethod'
        ]);

        return view('cms.pages.payments.show', compact('payment'));
    }

    public function refund(Payment $payment)
    {
        // Check if payment can be refunded
        if (!$payment->isCompleted()) {
            return redirect()->back()
                ->with('error', 'Only completed payments can be refunded.');
        }

        // In a real system, you'd call payment gateway API here
        $payment->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        // You might also want to update order status
        $payment->order->update(['status' => Order::STATUS_CANCELLED]);

        return redirect()->route('cms.payments.show', $payment)
            ->with('success', 'Payment refunded successfully.');
    }

    // ADD THIS MISSING METHOD
    public function search(Request $request)
    {
        $query = Payment::with(['order.user', 'order.store', 'paymentMethod']);

        // Search by transaction ID (partial match)
        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->get();
        
        // Get stats for the current search results
        $stats = [
            'total_payments' => $payments->total(),
            'total_revenue' => $payments->sum('amount'),
            'pending_payments' => $payments->where('status', 'pending')->count(),
            'failed_payments' => $payments->where('status', 'failed')->count(),
        ];

        return view('cms.pages.payments.index', compact('payments', 'stats'));
    }
}
