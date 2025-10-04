<?php

namespace App\Http\Controllers\CMS;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with(['user', 'cartItems.item'])
            ->latest()
            ->paginate(10);

        return view('cms.carts.index', compact('carts'));
    }

    public function show(Cart $cart)
    {
        $cart->load(['user', 'cartItems.item']);
        return view('cms.carts.show', compact('cart'));
    }

    public function edit(Cart $cart)
    {
        $cart->load(['user', 'cartItems.item']);
        return view('cms.carts.edit', compact('cart'));
    }

    public function update(Request $request, Cart $cart)
    {
        $validated = $request->validate([
            'cart_total' => 'required|numeric|min:0',
        ]);

        $cart->update($validated);

        return redirect()->route('cms.carts.index')
            ->with('success', 'Cart updated successfully.');
    }

    public function destroy(Cart $cart)
    {
        $cart->delete();

        return redirect()->route('cms.carts.index')
            ->with('success', 'Cart deleted successfully.');
    }

    /**
     * Clear all items from a cart
     */
    public function clearCart(Cart $cart)
    {
        $cart->clear();

        return redirect()->back()
            ->with('success', 'Cart cleared successfully.');
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request, Cart $cart)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'selected_size' => 'nullable|string',
            'selected_color' => 'nullable|string',
        ]);

        // Get the current item price
        $item = Item::findOrFail($validated['item_id']);
        $itemPrice = $item->price;

        // Check if item already exists in cart with same variants
        $existingCartItem = CartItem::where('cart_id', $cart->id)
            ->where('item_id', $validated['item_id'])
            ->where('selected_size', $validated['selected_size'])
            ->where('selected_color', $validated['selected_color'])
            ->first();

        if ($existingCartItem) {
            // Update quantity if already exists
            $existingCartItem->update([
                'quantity' => $existingCartItem->quantity + $validated['quantity']
            ]);
        } else {
            // Create new cart item
            CartItem::create([
                'cart_id' => $cart->id,
                'item_id' => $validated['item_id'],
                'quantity' => $validated['quantity'],
                'selected_size' => $validated['selected_size'],
                'selected_color' => $validated['selected_color'],
                'item_price' => $itemPrice,
            ]);
        }

        // Update cart total
        $cart->updateTotal();

        return redirect()->back()
            ->with('success', 'Item added to cart successfully.');
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Cart $cart, CartItem $cartItem)
    {
        if ($cartItem->cart_id !== $cart->id) {
            return redirect()->back()
                ->with('error', 'Cart item not found in this cart.');
        }

        $cartItem->delete();
        $cart->updateTotal();

        return redirect()->back()
            ->with('success', 'Item removed from cart successfully.');
    }

    /**
     * Update cart item quantity
     */
    public function updateItemQuantity(Request $request, Cart $cart, CartItem $cartItem)
    {
        if ($cartItem->cart_id !== $cart->id) {
            return redirect()->back()
                ->with('error', 'Cart item not found in this cart.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->updateQuantity($validated['quantity']);

        return redirect()->back()
            ->with('success', 'Cart item quantity updated successfully.');
    }
}
