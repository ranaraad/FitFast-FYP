<?php

namespace App\Http\Controllers\CMS;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'store', 'orderItems.item'])
            ->latest()
            ->paginate(10);

        return view('cms.orders.index', compact('orders'));
    }

    public function create()
    {
        $users = User::all();
        $stores = Store::all();
        return view('cms.orders.create', compact('users', 'stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'store_id' => 'required|exists:stores,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        Order::create($validated);

        return redirect()->route('cms.orders.index')
            ->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $order->load(['user', 'store', 'orderItems.item', 'payments', 'delivery']);
        return view('cms.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $users = User::all();
        $stores = Store::all();
        $order->load('orderItems');
        return view('cms.orders.edit', compact('order', 'users', 'stores'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'store_id' => 'required|exists:stores,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        $order->update($validated);

        return redirect()->route('cms.orders.index')
            ->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('cms.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        $order->update($validated);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }
}
