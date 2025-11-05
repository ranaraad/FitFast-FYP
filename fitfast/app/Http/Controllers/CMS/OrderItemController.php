<?php

namespace App\Http\Controllers\CMS;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderItemController extends Controller
{
    /**
     * Display all order items
     */
    public function all()
    {
        $orderItems = OrderItem::with(['order', 'item'])
            ->latest()
            ->get();

        return view('cms.pages.order-items.index', compact('orderItems'));
    }

    /**
     * Display order items for a specific order
     */
    public function index(Order $order)
    {
        $orderItems = $order->orderItems()->with('item')->get();
        return view('cms.pages.order-items.index', compact('orderItems', 'order'));
    }

    /**
     * Show the form for creating a new order item
     */
    public function create(Order $order)
    {
        $items = Item::with('store')->where('stock_quantity', '>', 0)->get();
        return view('cms.pages.order-items.create', compact('order', 'items'));
    }

    /**
     * Store a newly created order item
     */
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'selected_size' => 'required|string|max:10',
            'selected_color' => 'required|string|max:50',
            'selected_brand' => 'nullable|string|max:100',
        ]);

        // Get the item to check stock and get current price
        $item = Item::findOrFail($validated['item_id']);

        // Check if item is in stock for the selected size
        if (!$item->isSizeInStock($validated['selected_size']) ||
            !$item->isColorInStock($validated['selected_color'], $validated['quantity'])) {
            return redirect()->back()
                ->with('error', 'Selected item is not available in the requested quantity, size, or color.')
                ->withInput();
        }

        // Add unit price from the item
        $validated['unit_price'] = $item->price;
        $validated['order_id'] = $order->id;

        OrderItem::create($validated);

        // Update order total amount
        $this->updateOrderTotal($order);

        return redirect()->route('cms.orders.order-items.index', $order)
            ->with('success', 'Order item added successfully.');
    }

    /**
     * Display the specified order item
     */
    public function show(Order $order, OrderItem $orderItem)
    {
        $orderItem->load(['order', 'item']);
        return view('cms.pages.order-items.show', compact('orderItem', 'order'));
    }

    /**
     * Show the form for editing the specified order item
     */
    public function edit(Order $order, OrderItem $orderItem)
    {
        $items = Item::with('store')->where('stock_quantity', '>', 0)->get();
        $orderItem->load('item');
        return view('cms.pages.order-items.edit', compact('orderItem', 'order', 'items'));
    }

    /**
     * Update the specified order item
     */
    public function update(Request $request, Order $order, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'selected_size' => 'required|string|max:10',
            'selected_color' => 'required|string|max:50',
            'selected_brand' => 'nullable|string|max:100',
        ]);

        // Get the item to check stock and get current price
        $item = Item::findOrFail($validated['item_id']);

        // Check stock availability (considering current quantity vs new quantity)
        $quantityChange = $validated['quantity'] - $orderItem->quantity;

        if ($quantityChange > 0) {
            if (!$item->isSizeInStock($validated['selected_size']) ||
                !$item->isColorInStock($validated['selected_color'], $quantityChange)) {
                return redirect()->back()
                    ->with('error', 'Insufficient stock for the requested quantity, size, or color.')
                    ->withInput();
            }
        }

        // Update unit price if item changed
        if ($orderItem->item_id != $validated['item_id']) {
            $validated['unit_price'] = $item->price;
        }

        $orderItem->update($validated);

        // Update order total amount
        $this->updateOrderTotal($order);

        return redirect()->route('cms.orders.order-items.index', $order)
            ->with('success', 'Order item updated successfully.');
    }

    /**
     * Remove the specified order item
     */
    public function destroy(Order $order, OrderItem $orderItem)
    {
        $orderItem->delete();

        // Update order total amount
        $this->updateOrderTotal($order);

        return redirect()->route('cms.orders.order-items.index', $order)
            ->with('success', 'Order item deleted successfully.');
    }

    /**
     * Update order total amount based on order items
     */
    private function updateOrderTotal(Order $order)
    {
        $total = $order->orderItems->sum(function ($orderItem) {
            return $orderItem->unit_price * $orderItem->quantity;
        });

        $order->update(['total_amount' => $total]);
    }
}
