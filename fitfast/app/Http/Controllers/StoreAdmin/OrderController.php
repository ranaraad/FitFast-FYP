<?php

namespace App\Http\Controllers\StoreAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\User;
use App\Models\Store;
use App\Models\Item;
use App\Models\OrderItem;
use App\Models\Delivery;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = 1; // Temporary - this should be the logged-in store admin user ID
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $orders = Order::whereIn('store_id', $managedStoreIds)
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->store_id, function($query, $storeId) use ($managedStoreIds) {
                // Only allow filtering by stores that the user manages
                if (in_array($storeId, $managedStoreIds->toArray())) {
                    return $query->where('store_id', $storeId);
                }
                return $query;
            })
            ->when($request->search, function($query, $search) {
                return $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            })
            ->with(['user', 'store', 'orderItems.item'])
            ->latest()
            ->paginate(25);

        $stores = Store::where('user_id', $userId)->get();
        $statuses = Order::STATUSES;

        // Summary statistics
        $summary = [
            'total_orders' => $orders->total(),
            'pending_orders' => Order::whereIn('store_id', $managedStoreIds)
                ->where('status', Order::STATUS_PENDING)
                ->count(),
            'processing_orders' => Order::whereIn('store_id', $managedStoreIds)
                ->where('status', Order::STATUS_PROCESSING)
                ->count(),
            'completed_orders' => Order::whereIn('store_id', $managedStoreIds)
                ->where('status', Order::STATUS_DELIVERED)
                ->count(),
            'total_revenue' => Order::whereIn('store_id', $managedStoreIds)
                ->where('status', Order::STATUS_DELIVERED)
                ->sum('total_amount'),
        ];

        return view('cms.pages.store-admin.orders.index', compact('orders', 'stores', 'statuses', 'summary'));
    }


    public function show(Order $order)
    {
        // Check if order belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($order->store_id)) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['user', 'store', 'orderItems.item.store', 'delivery', 'payment']);
        return view('cms.pages.store-admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        // Check if order belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($order->store_id)) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['orderItems.item', 'delivery', 'payment']);
        $users = User::all();
        $stores = Store::where('user_id', $userId)->where('status', 'active')->get();
        $statuses = Order::STATUSES;

        return view('cms.pages.store-admin.orders.edit', compact('order', 'users', 'stores', 'statuses'));
    }

    public function update(Request $request, Order $order)
    {
        // Check if order belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($order->store_id)) {
            abort(403, 'Unauthorized access to this order.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'store_id' => 'required|exists:stores,id',
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
            'total_amount' => 'required|numeric|min:0',
            'delivery_address' => 'required|string|max:500',
            'delivery_status' => 'required|in:pending,shipped,delivered,failed',
        ]);

        // Verify the new store belongs to the user
        $store = Store::where('id', $validated['store_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        // Update order
        $order->update([
            'user_id' => $validated['user_id'],
            'store_id' => $validated['store_id'],
            'status' => $validated['status'],
            'total_amount' => $validated['total_amount'],
        ]);

        // Update delivery
        if ($order->delivery) {
            $order->delivery->update([
                'address' => $validated['delivery_address'],
                'status' => $validated['delivery_status'],
            ]);
        }

        // Remove payment method update since it's disabled in the form
        // Store admins shouldn't be able to change payment methods for existing orders

        return redirect()->route('store-admin.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        // Check if order belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($order->store_id)) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Prevent deletion of orders that are already processing or beyond
        if (!$order->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'Cannot delete order that is already being processed.');
        }

        // Use transaction for safety
        return DB::transaction(function () use ($order) {
            // SAFELY restore stock for order items
            foreach ($order->orderItems as $orderItem) {
                $item = $orderItem->item;
                $item->safeIncreaseStock($orderItem->quantity, $orderItem->selected_color);
            }

            $order->delete();

            return redirect()->route('store-admin.orders.index')
                ->with('success', 'Order deleted successfully and stock restored.');
        });
    }

    public function updateStatus(Request $request, Order $order)
    {
        // Check if order belongs to user's managed stores
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($order->store_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
        ]);

        $order->updateStatus($validated['status']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'new_status' => $validated['status']
            ]);
        }

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Get cart items for a specific cart
     */
    public function getCartItems(Cart $cart)
    {
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        try {
            $cart->load(['cartItems.item.store']);

            // Filter cart items to only include items from managed stores
            $items = $cart->cartItems->filter(function ($cartItem) use ($managedStoreIds) {
                return $managedStoreIds->contains($cartItem->item->store_id);
            })->map(function ($cartItem) {
                return [
                    'item_id' => $cartItem->item_id,
                    'name' => $cartItem->item->name,
                    'store_name' => $cartItem->item->store->name,
                    'quantity' => $cartItem->quantity,
                    'selected_size' => $cartItem->selected_size,
                    'selected_color' => $cartItem->selected_color,
                    'unit_price' => (float) $cartItem->item_price,
                    'total_price' => (float) $cartItem->total_price,
                ];
            });

            return response()->json($items->values()); // Reset keys for JSON
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load cart items: ' . $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $orders = Order::whereIn('store_id', $managedStoreIds)
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->store_id, function($query, $storeId) use ($managedStoreIds) {
                if (in_array($storeId, $managedStoreIds->toArray())) {
                    return $query->where('store_id', $storeId);
                }
                return $query;
            })
            ->with(['user', 'store', 'orderItems.item'])
            ->get();

        // Generate CSV
        $fileName = 'orders-export-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // CSV Headers
            $headers = [
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Store Name',
                'Total Amount',
                'Status',
                'Order Date',
                'Items Count',
                'Items Details',
                'Delivery Address',
                'Payment Status'
            ];

            fputcsv($file, $headers);

            // CSV Data
            foreach ($orders as $order) {
                $itemsDetails = '';
                foreach ($order->orderItems as $index => $orderItem) {
                    $itemsDetails .= ($index > 0 ? ' | ' : '') .
                        $orderItem->item->name .
                        ' (Qty: ' . $orderItem->quantity .
                        ', Size: ' . ($orderItem->selected_size ?? 'N/A') .
                        ', Color: ' . $orderItem->selected_color .
                        ', Price: $' . number_format($orderItem->unit_price, 2) . ')';
                }

                $deliveryAddress = $order->delivery ? $order->delivery->address : 'N/A';
                $paymentStatus = $order->payment ? $order->payment->status : 'N/A';

                $row = [
                    $order->id,
                    $order->user->name,
                    $order->user->email,
                    $order->store->name,
                    '$' . number_format($order->total_amount, 2),
                    ucfirst($order->status),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->orderItems->count(),
                    $itemsDetails,
                    $deliveryAddress,
                    ucfirst($paymentStatus)
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Advanced export with more options
     */
    public function exportAdvanced(Request $request)
    {
        $userId = 1; // Temporary
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'export_type' => 'required|in:summary,detailed'
        ]);

        $orders = Order::whereIn('store_id', $managedStoreIds)
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->store_id, function($query, $storeId) use ($managedStoreIds) {
                if (in_array($storeId, $managedStoreIds->toArray())) {
                    return $query->where('store_id', $storeId);
                }
                return $query;
            })
            ->when($request->start_date, function($query, $startDate) {
                return $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($request->end_date, function($query, $endDate) {
                return $query->whereDate('created_at', '<=', $endDate);
            })
            ->with(['user', 'store', 'orderItems.item', 'delivery', 'payment'])
            ->get();

        $fileName = 'orders-export-' . ($request->export_type === 'detailed' ? 'detailed' : 'summary') . '-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($orders, $request) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            if ($request->export_type === 'detailed') {
                $this->generateDetailedExport($file, $orders);
            } else {
                $this->generateSummaryExport($file, $orders);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate detailed CSV export
     */
    private function generateDetailedExport($file, $orders)
    {
        // Detailed Headers
        $headers = [
            'Order ID',
            'Customer Name',
            'Customer Email',
            'Store Name',
            'Total Amount',
            'Status',
            'Order Date',
            'Item Name',
            'Item Quantity',
            'Item Size',
            'Item Color',
            'Unit Price',
            'Item Total',
            'Delivery Address',
            'Delivery Status',
            'Payment Status',
            'Payment Method'
        ];

        fputcsv($file, $headers);

        // Detailed Data - one row per order item
        foreach ($orders as $order) {
            $deliveryAddress = $order->delivery ? $order->delivery->address : 'N/A';
            $deliveryStatus = $order->delivery ? $order->delivery->status : 'N/A';
            $paymentStatus = $order->payment ? $order->payment->status : 'N/A';
            $paymentMethod = $order->payment && $order->payment->paymentMethod ? $order->payment->paymentMethod->type : 'N/A';

            foreach ($order->orderItems as $orderItem) {
                $row = [
                    $order->id,
                    $order->user->name,
                    $order->user->email,
                    $order->store->name,
                    '$' . number_format($order->total_amount, 2),
                    ucfirst($order->status),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $orderItem->item->name,
                    $orderItem->quantity,
                    $orderItem->selected_size ?? 'N/A',
                    $orderItem->selected_color,
                    '$' . number_format($orderItem->unit_price, 2),
                    '$' . number_format($orderItem->quantity * $orderItem->unit_price, 2),
                    $deliveryAddress,
                    ucfirst($deliveryStatus),
                    ucfirst($paymentStatus),
                    ucfirst($paymentMethod)
                ];

                fputcsv($file, $row);
            }

            // If no order items, still add a row for the order
            if ($order->orderItems->count() === 0) {
                $row = [
                    $order->id,
                    $order->user->name,
                    $order->user->email,
                    $order->store->name,
                    '$' . number_format($order->total_amount, 2),
                    ucfirst($order->status),
                    $order->created_at->format('Y-m-d H:i:s'),
                    'No items',
                    '0',
                    'N/A',
                    'N/A',
                    '$0.00',
                    '$0.00',
                    $deliveryAddress,
                    ucfirst($deliveryStatus),
                    ucfirst($paymentStatus),
                    ucfirst($paymentMethod)
                ];

                fputcsv($file, $row);
            }
        }
    }

    /**
     * Generate summary CSV export
     */
    private function generateSummaryExport($file, $orders)
    {
        // Summary Headers
        $headers = [
            'Order ID',
            'Customer Name',
            'Customer Email',
            'Store Name',
            'Total Amount',
            'Status',
            'Order Date',
            'Items Count',
            'Items Summary',
            'Delivery Address',
            'Delivery Status',
            'Payment Status'
        ];

        fputcsv($file, $headers);

        // Summary Data - one row per order
        foreach ($orders as $order) {
            $itemsSummary = '';
            foreach ($order->orderItems as $index => $orderItem) {
                $itemsSummary .= ($index > 0 ? '; ' : '') .
                    $orderItem->item->name .
                    ' (x' . $orderItem->quantity . ')';
            }

            $deliveryAddress = $order->delivery ? $order->delivery->address : 'N/A';
            $deliveryStatus = $order->delivery ? $order->delivery->status : 'N/A';
            $paymentStatus = $order->payment ? $order->payment->status : 'N/A';

            $row = [
                $order->id,
                $order->user->name,
                $order->user->email,
                $order->store->name,
                '$' . number_format($order->total_amount, 2),
                ucfirst($order->status),
                $order->created_at->format('Y-m-d H:i:s'),
                $order->orderItems->count(),
                $itemsSummary,
                $deliveryAddress,
                ucfirst($deliveryStatus),
                ucfirst($paymentStatus)
            ];

            fputcsv($file, $row);
        }
    }
}
