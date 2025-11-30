<?php

namespace App\Http\Controllers\CMS;

use App\Models\Order;
use App\Models\Cart;
use App\Models\User;
use App\Models\Store;
use App\Models\Item;
use App\Models\OrderItem;
use App\Models\Delivery;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'store', 'orderItems.item'])
            ->latest()
            ->get();

        return view('cms.pages.orders.index', compact('orders'));
    }

    public function create()
    {
        $users = User::where('role_id', '3')->get();
        $stores = Store::where('status', 'active')->get();
        $carts = Cart::with(['user', 'cartItems.item'])
            ->whereHas('cartItems')
            ->get();

        return view('cms.pages.orders.create', compact('users', 'stores', 'carts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'store_id' => 'required|exists:stores,id',
            'cart_id' => 'required|exists:carts,id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selected_size' => 'nullable|string|max:50',
            'items.*.selected_color' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'delivery_address' => 'required|string|max:500',
        ]);

        // Use database transaction for safety
        return DB::transaction(function () use ($validated) {
            // Calculate total amount from items
            $totalAmount = 0;
            $outOfStockItems = [];

            // PRE-CHECK: Verify all items have sufficient stock before processing
            foreach ($validated['items'] as $itemData) {
                $item = Item::find($itemData['item_id']);
                $totalAmount += $itemData['quantity'] * $itemData['unit_price'];

                if (!$item->canFulfillOrder($itemData['quantity'], $itemData['selected_color'])) {
                    $outOfStockItems[] = $item->name;
                }
            }

            // If any items are out of stock, abort the order
            if (!empty($outOfStockItems)) {
                return redirect()->back()
                    ->with('error', 'Some items are out of stock: ' . implode(', ', $outOfStockItems))
                    ->withInput();
            }

            // Create order
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'store_id' => $validated['store_id'],
                'total_amount' => $totalAmount,
                'status' => Order::STATUS_PENDING,
            ]);

            // Create order items and SAFELY decrease stock
            foreach ($validated['items'] as $itemData) {
                $item = Item::find($itemData['item_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'selected_size' => $itemData['selected_size'] ?? null,
                    'selected_color' => $itemData['selected_color'],
                    'unit_price' => $itemData['unit_price'],
                ]);

                // SAFELY decrease item stock - this prevents overselling
                $success = $item->safeDecreaseStock($itemData['quantity'], $itemData['selected_color']);

                if (!$success) {
                    // This should rarely happen due to our pre-check, but handle it just in case
                    throw new \Exception("Failed to decrease stock for {$item->name}. Item may be out of stock.");
                }
            }

            // Create delivery record
            Delivery::create([
                'order_id' => $order->id,
                'address' => $validated['delivery_address'],
                'status' => 'pending',
            ]);

            // Clear the cart after creating order
            $cart = Cart::find($validated['cart_id']);
            if ($cart) {
                $cart->clear();
            }

            return redirect()->route('cms.orders.show', $order)
                ->with('success', 'Order created successfully from cart.');
        });
    }

    public function show(Order $order)
    {
        $order->load(['user', 'store', 'orderItems.item.store', 'delivery', 'payment']);
        return view('cms.pages.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['orderItems.item', 'delivery', 'payment']);
        $users = User::all();
        $stores = Store::where('status', 'active')->get();
        $statuses = Order::STATUSES;

        return view('cms.pages.orders.edit', compact('order', 'users', 'stores', 'statuses'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'store_id' => 'required|exists:stores,id',
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
            'total_amount' => 'required|numeric|min:0',
            'delivery_address' => 'required|string|max:500',
            'delivery_status' => 'required|in:pending,shipped,delivered,failed',
        ]);

        // Use database transaction for safety
        return DB::transaction(function () use ($order, $validated) {
            // Update order
            $order->update([
                'user_id' => $validated['user_id'],
                'store_id' => $validated['store_id'],
                'status' => $validated['status'],
                'total_amount' => $validated['total_amount'],
            ]);

            // Update or create delivery
            if ($order->delivery) {
                $order->delivery->update([
                    'address' => $validated['delivery_address'],
                    'status' => $validated['delivery_status'],
                ]);
            } else {
                // Create delivery record if it doesn't exist
                Delivery::create([
                    'order_id' => $order->id,
                    'address' => $validated['delivery_address'],
                    'status' => $validated['delivery_status'],
                ]);
            }

            // Handle stock adjustments based on status changes
            $this->handleStockAdjustments($order, $validated['status']);

            return redirect()->route('cms.orders.show', $order)
                ->with('success', 'Order updated successfully.');
        });
    }

    /**
     * Handle stock adjustments when order status changes
     */
    private function handleStockAdjustments(Order $order, string $newStatus)
    {
        $oldStatus = $order->getOriginal('status');

        // If order was cancelled and is now being reactivated, decrease stock
        if (($oldStatus === Order::STATUS_CANCELLED) &&
            in_array($newStatus, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING])) {

            foreach ($order->orderItems as $orderItem) {
                $item = $orderItem->item;
                $item->safeDecreaseStock($orderItem->quantity, $orderItem->selected_color);
            }
        }
        // If order is being cancelled or refunded, restore stock
        elseif (in_array($newStatus, [Order::STATUS_CANCELLED]) &&
                !in_array($oldStatus, [Order::STATUS_CANCELLED])) {

            foreach ($order->orderItems as $orderItem) {
                $item = $orderItem->item;
                $item->safeIncreaseStock($orderItem->quantity, $orderItem->selected_color);
            }
        }
    }

    public function destroy(Order $order)
    {
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

            return redirect()->route('cms.orders.index')
                ->with('success', 'Order deleted successfully and stock restored.');
        });
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
        ]);

        $order->updateStatus($validated['status']);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Get cart items for a specific cart
     */

public function getCartItems(Cart $cart)
{
    try {
        $cart->load(['cartItems.item.store']);

        $items = $cart->cartItems->map(function ($cartItem) {
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

        return response()->json($items);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to load cart items: ' . $e->getMessage()], 500);
    }
}


}
