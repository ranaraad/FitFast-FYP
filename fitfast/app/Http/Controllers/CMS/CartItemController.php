<?php

namespace App\Http\Controllers\CMS;

use App\Models\CartItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartItemController extends Controller
{
    public function destroy(CartItem $cartItem)
    {
        $cart = $cartItem->cart;
        $cartItem->delete();
        $cart->updateTotal();

        return redirect()->route('cms.carts.show', $cart)
            ->with('success', 'Item removed from cart successfully.');
    }

    public function updateQuantity(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99'
        ]);

        $cartItem->updateQuantity($request->quantity);

        return redirect()->route('cms.carts.show', $cartItem->cart)
            ->with('success', 'Item quantity updated successfully.');
    }
}
