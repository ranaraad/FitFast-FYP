<?php

namespace App\Http\Controllers\CMS;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Models\Item;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index()
    {
        $orders = Order::with(['store', 'user', 'orderItems'])
            ->latest()
            ->paginate(10);

        return view('cms.pages.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $stores = Store::where('status', 'active')->get();
        
        // FIXED: Get all users (since we don't have role setup yet, or use a fallback)
        $users = User::all();
        
        // Alternative: If you have roles setup, use this:
        // $users = User::where('role_id', 2)->get(); // Assuming 2 is customer role
        // OR if you have a role relationship:
        // $users = User::whereHas('role', function($query) {
        //     $query->where('name', 'customer');
        // })->get();

        return view('cms.pages.orders.create', compact('stores', 'users'));
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'order_items' => 'required|array|min:1',
            'order_items.*.item_id' => 'required|exists:items,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.selected_size' => 'required|string|max:10',
            'order_items.*.selected_color' => 'required|string|max:50',
            'order_items.*.selected_brand' => 'nullable|string|max:100',
        ]);

        // Check if all items belong to the selected store
        $itemIds = collect($validated['order_items'])->pluck('item_id')->unique();
        $itemsFromStore = Item::where('store_id', $validated['store_id'])
            ->whereIn('id', $itemIds)
            ->count();

        if ($itemsFromStore !== count($itemIds)) {
            return redirect()->back()
                ->with('error', 'Some selected items do not belong to the chosen store.')
                ->withInput();
        }

        // Check stock availability for all items
        foreach ($validated['order_items'] as $orderItemData) {
            $item = Item::find($orderItemData['item_id']);
            
            if (!$item->isSizeInStock($orderItemData['selected_size']) || 
                !$item->isColorInStock($orderItemData['selected_color'], $orderItemData['quantity'])) {
                return redirect()->back()
                    ->with('error', "Item '{$item->name}' is not available in the requested quantity, size, or color.")
                    ->withInput();
            }
        }

        // Create the order
        $order = Order::create([
            'store_id' => $validated['store_id'],
            'user_id' => $validated['user_id'],
            'status' => $validated['status'],
            'total_amount' => 0, // Will be calculated after adding items
        ]);

        // Add order items and calculate total
        $totalAmount = 0;
        foreach ($validated['order_items'] as $orderItemData) {
            $item = Item::find($orderItemData['item_id']);
            
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'item_id' => $orderItemData['item_id'],
                'quantity' => $orderItemData['quantity'],
                'selected_size' => $orderItemData['selected_size'],
                'selected_color' => $orderItemData['selected_color'],
                'selected_brand' => $orderItemData['selected_brand'] ?? $item->brand ?? null,
                'unit_price' => $item->price,
            ]);

            $totalAmount += $orderItem->unit_price * $orderItem->quantity;

            // Update item stock (you might want to do this when order is confirmed)
            // $item->decrementStock($orderItemData['selected_size'], $orderItemData['selected_color'], $orderItemData['quantity']);
        }

        // Update order total
        $order->update(['total_amount' => $totalAmount]);

        return redirect()->route('cms.orders.show', $order)
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['store', 'user', 'orderItems.item', 'delivery', 'payments']);
        
        return view('cms.pages.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        $order->load(['orderItems.item']);
        $stores = Store::where('status', 'active')->get();
        
        // FIXED: Get all users
        $users = User::all();

        // Get available items from the order's store
        $availableItems = Item::where('store_id', $order->store_id)
            ->where('stock_quantity', '>', 0)
            ->with('store')
            ->get();

        return view('cms.pages.orders.edit', compact('order', 'stores', 'users', 'availableItems'));
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'order_items' => 'required|array|min:1',
            'order_items.*.id' => 'nullable|exists:order_items,id', // For existing items
            'order_items.*.item_id' => 'required|exists:items,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.selected_size' => 'required|string|max:10',
            'order_items.*.selected_color' => 'required|string|max:50',
            'order_items.*.selected_brand' => 'nullable|string|max:100',
            'order_items.*._remove' => 'nullable|boolean', // Flag to remove item
        ]);

        // Check if store changed and validate items belong to new store
        if ($order->store_id != $validated['store_id']) {
            $itemIds = collect($validated['order_items'])->pluck('item_id')->unique();
            $itemsFromStore = Item::where('store_id', $validated['store_id'])
                ->whereIn('id', $itemIds)
                ->count();

            if ($itemsFromStore !== count($itemIds)) {
                return redirect()->back()
                    ->with('error', 'Some selected items do not belong to the chosen store.')
                    ->withInput();
            }
        }

        // Update basic order info
        $order->update([
            'store_id' => $validated['store_id'],
            'user_id' => $validated['user_id'],
            'status' => $validated['status'],
        ]);

        // Process order items
        $existingOrderItemIds = $order->orderItems->pluck('id')->toArray();
        $updatedOrderItemIds = [];
        $totalAmount = 0;

        foreach ($validated['order_items'] as $orderItemData) {
            // Skip items marked for removal
            if (isset($orderItemData['_remove']) && $orderItemData['_remove']) {
                continue;
            }

            $item = Item::find($orderItemData['item_id']);

            // Check stock availability
            if (!$item->isSizeInStock($orderItemData['selected_size']) || 
                !$item->isColorInStock($orderItemData['selected_color'], $orderItemData['quantity'])) {
                return redirect()->back()
                    ->with('error', "Item '{$item->name}' is not available in the requested quantity, size, or color.")
                    ->withInput();
            }

            // Update or create order item
            if (isset($orderItemData['id']) && in_array($orderItemData['id'], $existingOrderItemIds)) {
                // Update existing order item
                $orderItem = OrderItem::find($orderItemData['id']);
                $orderItem->update([
                    'item_id' => $orderItemData['item_id'],
                    'quantity' => $orderItemData['quantity'],
                    'selected_size' => $orderItemData['selected_size'],
                    'selected_color' => $orderItemData['selected_color'],
                    'selected_brand' => $orderItemData['selected_brand'] ?? $item->brand ?? null,
                    'unit_price' => $item->price, // Update price in case it changed
                ]);
                $updatedOrderItemIds[] = $orderItemData['id'];
            } else {
                // Create new order item
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $orderItemData['item_id'],
                    'quantity' => $orderItemData['quantity'],
                    'selected_size' => $orderItemData['selected_size'],
                    'selected_color' => $orderItemData['selected_color'],
                    'selected_brand' => $orderItemData['selected_brand'] ?? $item->brand ?? null,
                    'unit_price' => $item->price,
                ]);
                $updatedOrderItemIds[] = $orderItem->id;
            }

            $totalAmount += $orderItem->unit_price * $orderItem->quantity;
        }

        // Remove order items that weren't in the updated list
        $itemsToRemove = array_diff($existingOrderItemIds, $updatedOrderItemIds);
        if (!empty($itemsToRemove)) {
            OrderItem::whereIn('id', $itemsToRemove)->delete();
        }

        // Update order total
        $order->update(['total_amount' => $totalAmount]);

        return redirect()->route('cms.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order)
    {
        // Check if order can be deleted (e.g., not shipped/delivered)
        if (in_array($order->status, ['shipped', 'delivered'])) {
            return redirect()->back()
                ->with('error', 'Cannot delete order that has been shipped or delivered.');
        }

        $order->delete();

        return redirect()->route('cms.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Get items for a specific store (AJAX)
     */
    public function getStoreItems(Store $store)
    {
        $items = Item::where('store_id', $store->id)
            ->where('stock_quantity', '>', 0)
            ->with(['category'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'stock_quantity' => $item->stock_quantity,
                    'color_variants' => $item->color_variants ?? [],
                    'size_stock' => $item->size_stock ?? [],
                    'sizing_data' => $item->sizing_data,
                    'category' => $item->category->name,
                    'available_sizes' => array_keys(array_filter($item->size_stock ?? [])),
                    'available_colors' => array_keys($item->color_variants ?? []),
                ];
            });

        return response()->json($items);
    }

    /**
     * Get item details (AJAX)
     */
    public function getItemDetails(Item $item)
    {
        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'stock_quantity' => $item->stock_quantity,
            'color_variants' => $item->color_variants ?? [],
            'size_stock' => $item->size_stock ?? [],
            'sizing_data' => $item->sizing_data,
            'available_sizes' => array_keys(array_filter($item->size_stock ?? [])),
            'available_colors' => array_keys($item->color_variants ?? []),
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }
}