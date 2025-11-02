<?php

namespace App\Http\Controllers\StoreAdmin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $userId = 1; // Temporary - this should be the logged-in store admin user ID
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $deliveries = Delivery::whereHas('order', function($query) use ($managedStoreIds) {
                $query->whereIn('store_id', $managedStoreIds);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->store_id, function($query, $storeId) use ($managedStoreIds) {
                // Only allow filtering by stores that the user manages
                if (in_array($storeId, $managedStoreIds->toArray())) {
                    return $query->whereHas('order', function($q) use ($storeId) {
                        $q->where('store_id', $storeId);
                    });
                }
                return $query;
            })
            ->when($request->search, function($query, $search) {
                return $query->where('tracking_id', 'like', "%{$search}%")
                           ->orWhereHas('order.user', function($q) use ($search) {
                               $q->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                           });
            })
            ->with(['order.user', 'order.store'])
            ->latest()
            ->paginate(25);

        $stores = Store::where('user_id', $userId)->get();
        $carriers = $this->getCarriers();

        // Summary statistics for managed stores only
        $stats = [
            'total_deliveries' => Delivery::whereHas('order', function($query) use ($managedStoreIds) {
                $query->whereIn('store_id', $managedStoreIds);
            })->count(),
            'pending_deliveries' => Delivery::whereHas('order', function($query) use ($managedStoreIds) {
                $query->whereIn('store_id', $managedStoreIds);
            })->pending()->count(),
            'active_deliveries' => Delivery::whereHas('order', function($query) use ($managedStoreIds) {
                $query->whereIn('store_id', $managedStoreIds);
            })->active()->count(),
            'delivered_deliveries' => Delivery::whereHas('order', function($query) use ($managedStoreIds) {
                $query->whereIn('store_id', $managedStoreIds);
            })->delivered()->count(),
        ];

        return view('cms.pages.store-admin.deliveries.index', compact('deliveries', 'stats', 'stores', 'carriers'));
    }

    public function create()
    {
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        // Only show orders from managed stores that don't have deliveries yet
        $orders = Order::whereIn('store_id', $managedStoreIds)
            ->whereDoesntHave('delivery')
            ->whereIn('status', ['confirmed', 'processing'])
            ->with(['user', 'store'])
            ->get();

        $carriers = $this->getCarriers();

        return view('cms.pages.store-admin.deliveries.create', compact('orders', 'carriers'));
    }

    public function store(Request $request)
    {
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id|unique:deliveries,order_id',
            'tracking_id' => 'required|string|max:255',
            'carrier' => 'required|string|max:255',
            'estimated_delivery' => 'required|date|after:today',
            'address' => 'required|string|max:500',
        ]);

        // Verify that the order belongs to a managed store
        $order = Order::where('id', $validated['order_id'])
            ->whereIn('store_id', $managedStoreIds)
            ->firstOrFail();

        // Use transaction for safety
        return DB::transaction(function () use ($validated, $order) {
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
            $order->update(['status' => Order::STATUS_SHIPPED]);

            return redirect()->route('store-admin.deliveries.index')
                ->with('success', 'Delivery created and order marked as shipped successfully.');
        });
    }

    public function show(Delivery $delivery)
    {
        // Check if delivery belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($delivery->order->store_id)) {
            abort(403, 'Unauthorized access to this delivery.');
        }

        $delivery->load(['order.user', 'order.store', 'order.orderItems.item']);

        return view('cms.pages.store-admin.deliveries.show', compact('delivery'));
    }

    public function edit(Delivery $delivery)
    {
        // Check if delivery belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($delivery->order->store_id)) {
            abort(403, 'Unauthorized access to this delivery.');
        }

        $carriers = $this->getCarriers();
        $statuses = [
            'pending' => 'Pending',
            'shipped' => 'Shipped',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'failed' => 'Failed'
        ];

        return view('cms.pages.store-admin.deliveries.edit', compact('delivery', 'carriers', 'statuses'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        // Check if delivery belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($delivery->order->store_id)) {
            abort(403, 'Unauthorized access to this delivery.');
        }

        $validated = $request->validate([
            'tracking_id' => 'required|string|max:255',
            'carrier' => 'required|string|max:255',
            'estimated_delivery' => 'required|date',
            'address' => 'required|string|max:500',
            'status' => 'required|in:pending,shipped,in_transit,out_for_delivery,delivered,failed',
        ]);

        $oldStatus = $delivery->status;

        // Use transaction for safety
        return DB::transaction(function () use ($delivery, $validated, $oldStatus) {
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

            return redirect()->route('store-admin.deliveries.index')
                ->with('success', 'Delivery updated successfully.');
        });
    }

    public function destroy(Delivery $delivery)
    {
        // Check if delivery belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($delivery->order->store_id)) {
            abort(403, 'Unauthorized access to this delivery.');
        }

        // Only allow deletion if delivery is pending or failed
        if (!in_array($delivery->status, ['pending', 'failed'])) {
            return redirect()->back()
                ->with('error', 'Cannot delete delivery that has been shipped or delivered.');
        }

        $delivery->delete();

        return redirect()->route('store-admin.deliveries.index')
            ->with('success', 'Delivery deleted successfully.');
    }

    public function search(Request $request)
    {
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $query = Delivery::whereHas('order', function($query) use ($managedStoreIds) {
            $query->whereIn('store_id', $managedStoreIds);
        });

        if ($request->filled('tracking_id')) {
            $query->where('tracking_id', 'like', '%' . $request->tracking_id . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('carrier')) {
            $query->where('carrier', $request->carrier);
        }

        if ($request->filled('store_id') && in_array($request->store_id, $managedStoreIds->toArray())) {
            $query->whereHas('order', function($q) use ($request) {
                $q->where('store_id', $request->store_id);
            });
        }

        $deliveries = $query->with(['order.user', 'order.store'])->latest()->paginate(25);

        $stores = Store::where('user_id', $userId)->get();
        $carriers = $this->getCarriers();

        $stats = [
            'total_deliveries' => $deliveries->total(),
            'pending_deliveries' => $deliveries->where('status', 'pending')->count(),
            'active_deliveries' => $deliveries->whereIn('status', ['shipped', 'in_transit', 'out_for_delivery'])->count(),
            'delivered_deliveries' => $deliveries->where('status', 'delivered')->count(),
        ];

        return view('cms.pages.store-admin.deliveries.index', compact('deliveries', 'stats', 'stores', 'carriers'));
    }

    public function markAsDelivered(Delivery $delivery)
    {
        // Check if delivery belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($delivery->order->store_id)) {
            return redirect()->back()
                ->with('error', 'Unauthorized access to this delivery.');
        }

        if ($delivery->isCompleted()) {
            return redirect()->back()
                ->with('error', 'Delivery is already marked as delivered.');
        }

        // Use transaction for safety
        return DB::transaction(function () use ($delivery) {
            $delivery->markAsDelivered();
            $delivery->order->update(['status' => Order::STATUS_DELIVERED]);

            return redirect()->route('store-admin.deliveries.index')
                ->with('success', 'Delivery marked as delivered successfully.');
        });
    }

    public function updateTracking(Request $request, Delivery $delivery)
    {
        // Check if delivery belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($delivery->order->store_id)) {
            return redirect()->back()
                ->with('error', 'Unauthorized access to this delivery.');
        }

        $validated = $request->validate([
            'tracking_id' => 'required|string|max:255',
            'carrier' => 'required|string|max:255',
        ]);

        $delivery->update($validated);

        return redirect()->route('store-admin.deliveries.index')
            ->with('success', 'Tracking information updated successfully.');
    }

    public function addTracking(Request $request, Delivery $delivery)
    {
        // Check if delivery belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($delivery->order->store_id)) {
            return redirect()->back()
                ->with('error', 'Unauthorized access to this delivery.');
        }

        $validated = $request->validate([
            'tracking_id' => 'required|string|max:255',
            'carrier' => 'required|string|max:255',
        ]);

        // Use transaction for safety
        return DB::transaction(function () use ($delivery, $validated) {
            $delivery->update([
                'tracking_id' => $validated['tracking_id'],
                'carrier' => $validated['carrier'],
                'status' => 'shipped', // Update status to shipped when adding tracking
            ]);

            // Also update order status if it's still pending
            if ($delivery->order->status === 'confirmed') {
                $delivery->order->update(['status' => Order::STATUS_SHIPPED]);
            }

            return redirect()->route('store-admin.deliveries.index')
                ->with('success', 'Tracking information added and delivery marked as shipped.');
        });
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        // Check if delivery belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($delivery->order->store_id)) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized access to this delivery.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,shipped,in_transit,out_for_delivery,delivered,failed',
            'tracking_id' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:255',
        ]);

        $oldStatus = $delivery->status;

        // Use transaction for safety
        return DB::transaction(function () use ($delivery, $validated, $oldStatus, $request) {
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

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delivery status updated successfully.',
                    'new_status' => $validated['status']
                ]);
            }

            return redirect()->route('store-admin.deliveries.index')
                ->with('success', 'Delivery status updated successfully.');
        });
    }

    /**
     * Get available carriers
     */
    private function getCarriers()
    {
        return [
            'UPS' => 'UPS',
            'FedEx' => 'FedEx',
            'DHL' => 'DHL',
            'USPS' => 'USPS',
            'Local Courier' => 'Local Courier',
        ];
    }

    public function export(Request $request)
    {
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $deliveries = Delivery::whereHas('order', function($query) use ($managedStoreIds) {
                $query->whereIn('store_id', $managedStoreIds);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->store_id, function($query, $storeId) use ($managedStoreIds) {
                if (in_array($storeId, $managedStoreIds->toArray())) {
                    return $query->whereHas('order', function($q) use ($storeId) {
                        $q->where('store_id', $storeId);
                    });
                }
                return $query;
            })
            ->with(['order.user', 'order.store'])
            ->get();

        // For now, return JSON. You can implement CSV/Excel export later
        return response()->json($deliveries);
    }
}
