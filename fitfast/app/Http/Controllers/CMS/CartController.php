<?php

namespace App\Http\Controllers\CMS;

use App\Models\Cart;
use App\Models\User;
use App\Models\Item;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with(['user', 'cartItems.item'])
            ->latest()
            ->paginate(10);

        return view('cms.pages.carts.index', compact('carts'));
    }

    public function create()
    {
        $users = User::all();
        $items = Item::with('store')->get();
        return view('cms.pages.carts.create', compact('users', 'items'));
    }

    public function show(Cart $cart)
    {
        $cart->load(['user', 'cartItems.item.store']);
        return view('cms.pages.carts.show', compact('cart'));
    }

    public function edit(Cart $cart)
    {
        $cart->load(['user', 'cartItems.item']);
        $users = User::all();
        $items = Item::with('store')->get();

        return view('cms.pages.carts.edit', compact('cart', 'users', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selected_size' => 'nullable|string|max:50',
            'items.*.selected_color' => 'required|string|max:50', // Now required
        ]);

        // Check if user already has a cart
        $cart = Cart::where('user_id', $validated['user_id'])->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => $validated['user_id']]);
        }

        $errors = [];

        // Add items to cart with stock validation
        foreach ($validated['items'] as $index => $itemData) {
            $item = Item::find($itemData['item_id']);

            // Check color stock availability
            if (!$this->checkColorStockAvailability($item, $itemData['selected_color'], $itemData['quantity'])) {
                $errors[] = "Item '{$item->name}' color '{$itemData['selected_color']}' is out of stock or insufficient quantity.";
                continue;
            }

            CartItem::create([
                'cart_id' => $cart->id,
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'selected_size' => $itemData['selected_size'] ?? null,
                'selected_color' => $itemData['selected_color'],
                'item_price' => $item->price,
            ]);

            // Decrease color stock
            $this->decreaseColorStock($item, $itemData['selected_color'], $itemData['quantity']);
        }

        $cart->updateTotal();

        if (!empty($errors)) {
            return redirect()->back()
                ->with('warning', 'Cart created with some items unavailable: ' . implode(' ', $errors))
                ->withInput();
        }

        return redirect()->route('cms.carts.index')
            ->with('success', 'Cart created successfully.');
    }

    public function update(Request $request, Cart $cart)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.id' => 'nullable|exists:cart_items,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selected_size' => 'nullable|string|max:50',
            'items.*.selected_color' => 'required|string|max:50', // Now required
            'items_to_remove' => 'nullable|array',
        ]);

        // Update user if changed
        if ($cart->user_id != $validated['user_id']) {
            $cart->update(['user_id' => $validated['user_id']]);
        }

        $errors = [];

        // First, restore stock for items being removed
        if (!empty($validated['items_to_remove'])) {
            $removedItems = CartItem::whereIn('id', $validated['items_to_remove'])->get();
            foreach ($removedItems as $removedItem) {
                $this->increaseColorStock($removedItem->item, $removedItem->selected_color, $removedItem->quantity);
            }
            CartItem::whereIn('id', $validated['items_to_remove'])->delete();
        }

        // Update or create items
        foreach ($validated['items'] as $itemData) {
            $item = Item::find($itemData['item_id']);

            if (isset($itemData['id'])) {
                // Update existing item
                $cartItem = CartItem::find($itemData['id']);
                if ($cartItem) {
                    // Restore old stock first
                    $this->increaseColorStock($cartItem->item, $cartItem->selected_color, $cartItem->quantity);

                    // Check new color stock availability
                    if (!$this->checkColorStockAvailability($item, $itemData['selected_color'], $itemData['quantity'])) {
                        $errors[] = "Item '{$item->name}' color '{$itemData['selected_color']}' is out of stock or insufficient quantity.";
                        continue;
                    }

                    $cartItem->update([
                        'quantity' => $itemData['quantity'],
                        'selected_size' => $itemData['selected_size'] ?? null,
                        'selected_color' => $itemData['selected_color'],
                        'item_price' => $item->price,
                    ]);

                    // Decrease new color stock
                    $this->decreaseColorStock($item, $itemData['selected_color'], $itemData['quantity']);
                }
            } else {
                // Create new item
                if (!$this->checkColorStockAvailability($item, $itemData['selected_color'], $itemData['quantity'])) {
                    $errors[] = "Item '{$item->name}' color '{$itemData['selected_color']}' is out of stock or insufficient quantity.";
                    continue;
                }

                CartItem::create([
                    'cart_id' => $cart->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'selected_size' => $itemData['selected_size'] ?? null,
                    'selected_color' => $itemData['selected_color'],
                    'item_price' => $item->price,
                ]);

                $this->decreaseColorStock($item, $itemData['selected_color'], $itemData['quantity']);
            }
        }

        $cart->updateTotal();

        if (!empty($errors)) {
            return redirect()->back()
                ->with('warning', 'Cart updated with some items unavailable: ' . implode(' ', $errors))
                ->withInput();
        }

        return redirect()->route('cms.carts.show', $cart)
            ->with('success', 'Cart updated successfully.');
    }

        public function destroy(Cart $cart)
    {
        $cart->cartItems()->delete();
        $cart->delete();

        return redirect()->route('cms.carts.index')
            ->with('success', 'Cart cleared and deleted successfully.');
    }

    public function clearCart(Cart $cart)
    {
        $cart->clear();

        return redirect()->route('cms.carts.show', $cart)
            ->with('success', 'Cart cleared successfully.');
    }

    /**
     * Check if color stock is available
     */
    private function checkColorStockAvailability(Item $item, string $color, int $quantity): bool
    {
        return $item->isColorInStock($color, $quantity);
    }

    /**
     * Decrease color stock for an item
     */
    private function decreaseColorStock(Item $item, string $color, int $quantity): void
    {
        $item->decreaseColorStock($color, $quantity);
    }

    /**
     * Increase color stock for an item (when cart items are removed)
     */
    private function increaseColorStock(Item $item, string $color, int $quantity): void
    {
        $item->increaseColorStock($color, $quantity);
    }

   // Add to CartController.php
// In CartController.php
public function getUserCarts(User $user, Request $request)
{
    $storeId = $request->get('store_id');
    
    $query = Cart::with(['cartItems.item.store'])
        ->where('user_id', $user->id)
        ->whereHas('cartItems');
    
    // Filter by store if provided
    if ($storeId) {
        $query->whereHas('cartItems.item', function($q) use ($storeId) {
            $q->where('store_id', $storeId);
        });
    }
    
    $carts = $query->get()->map(function ($cart) {
        return [
            'id' => $cart->id,
            'total_items' => $cart->total_items,
            'cart_total' => $cart->cart_total,
            'formatted_total' => number_format($cart->cart_total, 2),
            'last_activity' => $cart->last_activity,
        ];
    });

    return response()->json($carts);
}
}
