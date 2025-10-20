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
        $deliveries = Delivery::with(['order.user', 'order.store'])
            ->latest()
            ->paginate(10);

        $stats = [
            'total_deliveries' => Delivery::count(),
            'pending_deliveries' => Delivery::pending()->count(),
            'active_deliveries' => Delivery::active()->count(),
            'delivered_deliveries' => Delivery::delivered()->count(),
        ];

        return view('cms.pages.deliveries.index', compact('deliveries', 'stats'));
    }

    public function create()
    {
        $orders = Order::whereDoesntHave('delivery')
            ->whereIn('status', ['confirmed', 'processing'])
            ->with(['user', 'store'])
            ->get();

        $carriers = [
            'UPS' => 'UPS',
            'FedEx' => 'FedEx',
            'DHL' => 'DHL',
            'USPS' => 'USPS',
            'Local Courier' => 'Local Courier',
        ];

        return view('cms.pages.deliveries.create', compact('orders', 'carriers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id|unique:deliveries,order_id',
            'tracking_id' => 'required|string|max:255',
            'carrier' => 'required|string|max:255',
            'estimated_delivery' => 'required|date|after:today',
            'address' => 'required|string|max:500',
        ]);

        // Create delivery and mark as shipped
        $delivery = Delivery::create([
            'order_id' => $validated['order_id'],
            'tracking_id' => $validated['tracking_id'],
            'carrier' => $validated['carrier'],
            'estimated_delivery' => $validated['estimated_delivery'],
            'address' => $validated['address'],
            'status' => 'shipped',
        ]);

        // Update order status to shipped
        $delivery->order->update(['status' => Order::STATUS_SHIPPED]);

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Delivery created and order marked as shipped successfully.');
    }

    public function destroy(Delivery $delivery)
    {
        // Only allow deletion if delivery is pending or failed
        if (!in_array($delivery->status, ['pending', 'failed'])) {
            return redirect()->back()
                ->with('error', 'Cannot delete delivery that has been shipped or delivered.');
        }

        $delivery->delete();

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Delivery deleted successfully.');
    }

    public function search(Request $request)
    {
        $query = Delivery::with(['order.user', 'order.store']);

        if ($request->filled('tracking_id')) {
            $query->where('tracking_id', 'like', '%' . $request->tracking_id . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('carrier')) {
            $query->where('carrier', $request->carrier);
        }

        $deliveries = $query->latest()->paginate(10);

        $stats = [
            'total_deliveries' => $deliveries->total(),
            'pending_deliveries' => $deliveries->where('status', 'pending')->count(),
            'active_deliveries' => $deliveries->whereIn('status', ['shipped', 'in_transit', 'out_for_delivery'])->count(),
            'delivered_deliveries' => $deliveries->where('status', 'delivered')->count(),
        ];

        return view('cms.pages.deliveries.index', compact('deliveries', 'stats'));
    }

    public function markAsDelivered(Delivery $delivery)
    {
        if ($delivery->isCompleted()) {
            return redirect()->back()
                ->with('error', 'Delivery is already marked as delivered.');
        }

        $delivery->markAsDelivered();
        $delivery->order->update(['status' => Order::STATUS_DELIVERED]);

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Delivery marked as delivered successfully.');
    }

    public function updateTracking(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'tracking_id' => 'required|string|max:255',
            'carrier' => 'required|string|max:255',
        ]);

        $delivery->update($validated);

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Tracking information updated successfully.');
    }

    public function addTracking(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'tracking_id' => 'required|string|max:255',
            'carrier' => 'required|string|max:255',
        ]);

        $delivery->update([
            'tracking_id' => $validated['tracking_id'],
            'carrier' => $validated['carrier'],
            'status' => 'shipped', // Update status to shipped when adding tracking
        ]);

        // Also update order status if it's still pending
        if ($delivery->order->status === 'confirmed') {
            $delivery->order->update(['status' => Order::STATUS_SHIPPED]);
        }

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Tracking information added and delivery marked as shipped.');
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,shipped,in_transit,out_for_delivery,delivered,failed',
            'tracking_id' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:255',
        ]);

        $oldStatus = $delivery->status;

        // Update delivery
        $delivery->update($validated);

        // Update order status based on delivery status
        if ($validated['status'] === 'delivered' && $oldStatus !== 'delivered') {
            $delivery->order->update(['status' => Order::STATUS_DELIVERED]);
            $delivery->update(['estimated_delivery' => now()]); // Set actual delivery time
        } elseif ($validated['status'] === 'shipped' && $oldStatus !== 'shipped') {
            $delivery->order->update(['status' => Order::STATUS_SHIPPED]);
        } elseif ($validated['status'] === 'failed' && $oldStatus !== 'failed') {
            $delivery->order->update(['status' => Order::STATUS_CANCELLED]);
        }

        return redirect()->route('cms.deliveries.index')
            ->with('success', 'Delivery status updated successfully.');
    }
}
