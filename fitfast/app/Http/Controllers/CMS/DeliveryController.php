<?php

namespace App\Http\Controllers\CMS;

use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = Delivery::with('order.user')
            ->latest()
            ->paginate(10);

        return view('cms.deliveries.index', compact('deliveries'));
    }

    public function create()
    {
        $orders = Order::whereDoesntHave('delivery')
            ->with('user')
            ->get();
        return view('cms.deliveries.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id|unique:deliveries,order_id',
            'tracking_id' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|date',
            'status' => 'required|in:pending,shipped,in_transit,out_for_delivery,delivered,failed',
            'address' => 'required|string',
        ]);

        Delivery::create($validated);

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Delivery created successfully.');
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['order.user', 'order.orderItems.item']);
        return view('cms.deliveries.show', compact('delivery'));
    }

    public function edit(Delivery $delivery)
    {
        $delivery->load('order');
        return view('cms.deliveries.edit', compact('delivery'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'tracking_id' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|date',
            'status' => 'required|in:pending,shipped,in_transit,out_for_delivery,delivered,failed',
            'address' => 'required|string',
        ]);

        $delivery->update($validated);

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Delivery updated successfully.');
    }

    public function destroy(Delivery $delivery)
    {
        $delivery->delete();

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Delivery deleted successfully.');
    }

    /**
     * Update delivery status
     */
    public function updateStatus(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,shipped,in_transit,out_for_delivery,delivered,failed',
        ]);

        $delivery->updateStatus($validated['status']);

        return redirect()->back()
            ->with('success', 'Delivery status updated successfully.');
    }

    /**
     * Mark delivery as shipped with tracking
     */
    public function markAsShipped(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'tracking_id' => 'required|string|max:255',
            'carrier' => 'required|string|max:255',
            'estimated_delivery' => 'nullable|date',
        ]);

        $delivery->markAsShipped(
            $validated['tracking_id'],
            $validated['carrier'],
            $validated['estimated_delivery']
        );

        return redirect()->back()
            ->with('success', 'Delivery marked as shipped with tracking information.');
    }

    /**
     * Mark delivery as delivered
     */
    public function markAsDelivered(Delivery $delivery)
    {
        $delivery->markAsDelivered();

        return redirect()->back()
            ->with('success', 'Delivery marked as delivered.');
    }

    /**
     * Get deliveries by status
     */
    public function byStatus($status)
    {
        $validStatuses = ['pending', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'failed'];

        if (!in_array($status, $validStatuses)) {
            return redirect()->route('cms.deliveries.index')
                ->with('error', 'Invalid status.');
        }

        $deliveries = Delivery::with('order.user')
            ->where('status', $status)
            ->latest()
            ->paginate(10);

        return view('cms.deliveries.index', compact('deliveries', 'status'));
    }
}
