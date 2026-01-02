<?php

namespace App\Http\Controllers\CMS;

use App\Models\Cart;
use App\Models\User;
use App\Models\Item;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with(['user', 'cartItems.item'])
            ->latest()
            ->get();

        return view('cms.pages.carts.index', compact('carts'));
    }

    public function create()
    {
        $users = User::where('role_id', '3')->get();
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
            'items.*.selected_size' => 'required|string|max:50',
            'items.*.selected_color' => 'required|string|max:50',
        ]);

        // Check if user already has a cart
        $cart = Cart::where('user_id', $validated['user_id'])->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => $validated['user_id']]);
        }

        $errors = [];
        $successfulItems = [];

        // Add items to cart with variant stock validation
        foreach ($validated['items'] as $index => $itemData) {
            $item = Item::find($itemData['item_id']);

            // Check color-size variant stock availability
            if (!$this->checkVariantStockAvailability($item, $itemData['selected_color'], $itemData['selected_size'], $itemData['quantity'])) {
                $errors[] = "Item '{$item->name}' in {$itemData['selected_color']}/{$itemData['selected_size']} has insufficient stock.";
                continue;
            }

            try {
                // Decrease variant stock using CartController method
                if (!$this->decreaseVariantStock($item, $itemData['selected_color'], $itemData['selected_size'], $itemData['quantity'])) {
                    throw new \Exception("Failed to decrease stock for {$item->name} in {$itemData['selected_color']}/{$itemData['selected_size']}");
                }

                // Create cart item
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'selected_size' => $itemData['selected_size'],
                    'selected_color' => $itemData['selected_color'],
                    'item_price' => $item->price,
                ]);

                $successfulItems[] = $cartItem;

            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        $cart->updateTotal();

        if (!empty($errors)) {
            // If there were any errors, rollback successful items too
            if (!empty($successfulItems)) {
                foreach ($successfulItems as $cartItem) {
                    // Restore stock for successful items before showing error
                    $item = Item::find($cartItem->item_id);
                    $this->increaseVariantStock($item, $cartItem->selected_color, $cartItem->selected_size, $cartItem->quantity);
                    $cartItem->delete();
                }
            }

            return redirect()->back()
                ->with('warning', 'Unable to add some items to cart: ' . implode(' ', $errors))
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
            'items.*.selected_size' => 'required|string|max:50',
            'items.*.selected_color' => 'required|string|max:50',
            'items_to_remove' => 'nullable|array',
        ]);

        // Update user if changed
        if ($cart->user_id != $validated['user_id']) {
            $cart->update(['user_id' => $validated['user_id']]);
        }

        $errors = [];
        $successfulUpdates = [];

        // First, restore stock for items being removed
        if (!empty($validated['items_to_remove'])) {
            $removedItems = CartItem::whereIn('id', $validated['items_to_remove'])->get();
            foreach ($removedItems as $removedItem) {
                $this->increaseVariantStock($removedItem->item, $removedItem->selected_color, $removedItem->selected_size, $removedItem->quantity);
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
                    // Track changes for stock adjustment
                    $oldQuantity = $cartItem->quantity;
                    $oldColor = $cartItem->selected_color;
                    $oldSize = $cartItem->selected_size;

                    $newQuantity = $itemData['quantity'];
                    $newColor = $itemData['selected_color'];
                    $newSize = $itemData['selected_size'];

                    // Check if anything changed
                    if ($oldQuantity != $newQuantity || $oldColor != $newColor || $oldSize != $newSize) {
                        // Restore old stock first
                        $this->increaseVariantStock($cartItem->item, $oldColor, $oldSize, $oldQuantity);

                        // Check new variant stock availability
                        if (!$this->checkVariantStockAvailability($item, $newColor, $newSize, $newQuantity)) {
                            $errors[] = "Item '{$item->name}' in {$newColor}/{$newSize} has insufficient stock.";
                            // Restore the original stock
                            $this->decreaseVariantStock($item, $oldColor, $oldSize, $oldQuantity);
                            continue;
                        }

                        // Decrease new variant stock
                        if (!$this->decreaseVariantStock($item, $newColor, $newSize, $newQuantity)) {
                            $errors[] = "Failed to update stock for {$item->name} in {$newColor}/{$newSize}";
                            continue;
                        }
                    }

                    $cartItem->update([
                        'quantity' => $newQuantity,
                        'selected_size' => $newSize,
                        'selected_color' => $newColor,
                        'item_price' => $item->price,
                    ]);

                    $successfulUpdates[] = $cartItem;
                }
            } else {
                // Create new item
                if (!$this->checkVariantStockAvailability($item, $itemData['selected_color'], $itemData['selected_size'], $itemData['quantity'])) {
                    $errors[] = "Item '{$item->name}' in {$itemData['selected_color']}/{$itemData['selected_size']} has insufficient stock.";
                    continue;
                }

                // Decrease variant stock
                if (!$this->decreaseVariantStock($item, $itemData['selected_color'], $itemData['selected_size'], $itemData['quantity'])) {
                    $errors[] = "Failed to decrease stock for {$item->name} in {$itemData['selected_color']}/{$itemData['selected_size']}";
                    continue;
                }

                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'selected_size' => $itemData['selected_size'],
                    'selected_color' => $itemData['selected_color'],
                    'item_price' => $item->price,
                ]);

                $successfulUpdates[] = $cartItem;
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
        // Restore stock for all items in the cart before deleting
        foreach ($cart->cartItems as $cartItem) {
            $this->increaseVariantStock($cartItem->item, $cartItem->selected_color, $cartItem->selected_size, $cartItem->quantity);
        }

        $cart->cartItems()->delete();
        $cart->delete();

        return redirect()->route('cms.carts.index')
            ->with('success', 'Cart cleared and deleted successfully.');
    }

    public function clearCart(Cart $cart)
    {
        // Restore stock for all items in the cart
        foreach ($cart->cartItems as $cartItem) {
            $this->increaseVariantStock($cartItem->item, $cartItem->selected_color, $cartItem->selected_size, $cartItem->quantity);
        }

        $cart->cartItems()->delete();
        $cart->updateTotal();

        return redirect()->route('cms.carts.show', $cart)
            ->with('success', 'Cart cleared successfully.');
    }

    /**
     * NEW METHODS FOR COLOR-SIZE VARIANT SYSTEM
     */

    private function checkVariantStockAvailability(Item $item, string $color, string $size, int $quantity): bool
    {
        $variants = $item->variants ?? [];

        foreach ($variants as $variant) {
            if (isset($variant['color'], $variant['size'], $variant['stock'])) {
                // Case-insensitive comparison
                if (strtolower($variant['color']) === strtolower($color) &&
                    strtoupper($variant['size']) === strtoupper($size)) {
                    return $variant['stock'] >= $quantity;
                }
            }
        }

        return false;
    }

    private function decreaseVariantStock(Item $item, string $color, string $size, int $quantity): bool
    {
        Log::info('Decreasing variant stock', [
            'item_id' => $item->id,
            'color' => $color,
            'size' => $size,
            'quantity' => $quantity,
            'variants_before' => $item->variants
        ]);

        $variants = $item->variants ?? [];
        $updated = false;

        foreach ($variants as $index => &$variant) {
            if (isset($variant['color'], $variant['size'], $variant['stock'])) {
                if (strtolower($variant['color']) === strtolower($color) &&
                    strtoupper($variant['size']) === strtoupper($size)) {

                    if ($variant['stock'] >= $quantity) {
                        $variant['stock'] -= $quantity;

                        // If stock becomes 0, remove the variant
                        if ($variant['stock'] <= 0) {
                            unset($variants[$index]);
                        }

                        $updated = true;
                        break;
                    }
                }
            }
        }

        if ($updated) {
            $item->variants = $variants;
            $item->updateAggregatedStock();

            Log::info('Decreasing variant stock successful', [
                'item_id' => $item->id,
                'variants_after' => $item->variants
            ]);

            return $item->save();
        }

        Log::error('Failed to decrease variant stock', [
            'item_id' => $item->id,
            'color' => $color,
            'size' => $size,
            'quantity' => $quantity
        ]);

        return false;
    }

    private function increaseVariantStock(Item $item, string $color, string $size, int $quantity): bool
    {
        Log::info('Increasing variant stock', [
            'item_id' => $item->id,
            'color' => $color,
            'size' => $size,
            'quantity' => $quantity
        ]);

        $variants = $item->variants ?? [];
        $found = false;

        foreach ($variants as &$variant) {
            if (isset($variant['color'], $variant['size'])) {
                if (strtolower($variant['color']) === strtolower($color) &&
                    strtoupper($variant['size']) === strtoupper($size)) {

                    $variant['stock'] = ($variant['stock'] ?? 0) + $quantity;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            // Create new variant entry
            $key = strtolower($color) . '_' . strtoupper($size);
            $variants[$key] = [
                'color' => $color,
                'size' => $size,
                'stock' => $quantity
            ];
        }

        $item->variants = $variants;
        $item->updateAggregatedStock();

        return $item->save();
    }
}
