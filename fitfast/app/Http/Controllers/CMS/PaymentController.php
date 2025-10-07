<?php

namespace App\Http\Controllers\CMS;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['order.user', 'paymentMethod'])
            ->latest()
            ->paginate(10);

        return view('cms.payments.index', compact('payments'));
    }

    public function create()
    {
        $orders = Order::whereDoesntHave('payments', function ($query) {
            $query->where('status', 'completed');
        })->with('user')->get();

        $paymentMethods = PaymentMethod::with('user')->get();

        return view('cms.payments.create', compact('orders', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0',
            'transaction_id' => 'nullable|string|max:255',
            'status' => 'required|in:pending,processing,completed,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        Payment::create($validated);

        return redirect()->route('cms.payments.index')
            ->with('success', 'Payment created successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['order.user', 'order.orderItems.item', 'paymentMethod.user']);
        return view('cms.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $payment->load(['order', 'paymentMethod']);
        $orders = Order::all();
        $paymentMethods = PaymentMethod::all();

        return view('cms.payments.edit', compact('payment', 'orders', 'paymentMethods'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0',
            'transaction_id' => 'nullable|string|max:255',
            'status' => 'required|in:pending,processing,completed,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return redirect()->route('cms.payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('cms.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Update payment status
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,refunded',
        ]);

        $payment->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', 'Payment status updated successfully.');
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $payment->markAsCompleted($validated['transaction_id']);

        return redirect()->back()
            ->with('success', 'Payment marked as completed.');
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $payment->markAsFailed($validated['notes']);

        return redirect()->back()
            ->with('success', 'Payment marked as failed.');
    }

    /**
     * Mark payment as refunded
     */
    public function markAsRefunded(Payment $payment)
    {
        $payment->markAsRefunded();

        return redirect()->back()
            ->with('success', 'Payment marked as refunded.');
    }

    /**
     * Get payments by status
     */
    public function byStatus($status)
    {
        $validStatuses = ['pending', 'processing', 'completed', 'failed', 'refunded'];

        if (!in_array($status, $validStatuses)) {
            return redirect()->route('cms.payments.index')
                ->with('error', 'Invalid status.');
        }

        $payments = Payment::with(['order.user', 'paymentMethod'])
            ->where('status', $status)
            ->latest()
            ->paginate(10);

        return view('cms.payments.index', compact('payments', 'status'));
    }
}
