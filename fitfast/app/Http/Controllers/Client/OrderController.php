<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Store a newly created order from the frontend checkout
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'delivery_address' => 'required|string|max:500',
            'contact' => 'required|array',
            'contact.fullName' => 'required|string|max:255',
            'contact.email' => 'required|email|max:255',
            'contact.phone' => 'nullable|string|max:20',
            'payment_method' => 'required|string|in:card,paypal,cash,cod',
            'card_last4' => 'nullable|string|max:4',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request) {
                // Create the order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'store_id' => $validated['store_id'],
                    'total_amount' => $validated['total_amount'],
                    'status' => 'pending',
                ]);

                // Create order items
                foreach ($validated['items'] as $itemData) {
                    $item = Item::findOrFail($itemData['id']);
                    
                    // Create order item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $itemData['id'],
                        'quantity' => $itemData['quantity'],
                        'selected_size' => $itemData['size'] ?? null,
                        'selected_color' => $itemData['color'] ?? null,
                        'unit_price' => $itemData['price'],
                    ]);

                    // Decrease stock safely
                    $item->safeDecreaseStock($itemData['quantity'], $itemData['color'] ?? null);
                }

                // Create delivery record
                Delivery::create([
                    'order_id' => $order->id,
                    'address' => $validated['delivery_address'],
                    'status' => 'pending',
                ]);

                // Create payment record
                $paymentType = $validated['payment_method'] === 'cod'
                    ? 'cash'
                    : $validated['payment_method'];

                $paymentMethod = PaymentMethod::query()
                    ->where('user_id', Auth::id())
                    ->where('type', $paymentType)
                    ->first();

                if (!$paymentMethod) {
                    $paymentDetails = [];

                    if ($paymentType === 'card') {
                        $paymentDetails = array_filter([
                            'label' => 'Card on file',
                            'last4' => $validated['card_last4'] ?? null,
                        ]);
                    } elseif ($paymentType === 'cash') {
                        $paymentDetails = ['label' => 'Cash on delivery'];
                    } elseif ($paymentType === 'paypal') {
                        $paymentDetails = ['label' => 'PayPal'];
                    }

                    $paymentMethod = PaymentMethod::create([
                        'user_id' => Auth::id(),
                        'type' => $paymentType,
                        'details' => $paymentDetails,
                        'is_default' => $paymentType === 'card',
                    ]);
                }

                $transactionId = $paymentType === 'card'
                    ? 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 10))
                    : null;

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $validated['total_amount'],
                    'status' => 'pending',
                    'transaction_id' => $transactionId,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'order' => [
                        'id' => $order->id,
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                    ],
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's orders
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['store', 'orderItems.item', 'delivery', 'payment'])
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'store_name' => $order->store->name,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'items_count' => $order->orderItems->count(),
                    'delivery_status' => $order->delivery->status ?? 'N/A',
                    'payment_status' => $order->payment->status ?? 'N/A',
                    'created_at' => $order->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Get a specific order
     */
    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['store', 'orderItems.item.images', 'delivery', 'payment'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'store' => [
                    'id' => $order->store->id,
                    'name' => $order->store->name,
                ],
                'items' => $order->orderItems->map(function ($orderItem) {
                    $primaryImage = $orderItem->item->primary_image;
                    return [
                        'id' => $orderItem->item->id,
                        'name' => $orderItem->item->name,
                        'quantity' => $orderItem->quantity,
                        'size' => $orderItem->selected_size,
                        'color' => $orderItem->selected_color,
                        'unit_price' => $orderItem->unit_price,
                        'total' => $orderItem->quantity * $orderItem->unit_price,
                        'image_url' => $primaryImage ? 
                            asset('storage/' . $primaryImage->image_path) : null,
                    ];
                }),
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'delivery' => [
                    'address' => $order->delivery->address ?? 'N/A',
                    'status' => $order->delivery->status ?? 'N/A',
                ],
                'payment' => [
                    'method' => optional($order->payment->paymentMethod)->type ?? 'N/A',
                    'status' => $order->payment->status ?? 'N/A',
                    'amount' => $order->payment->amount ?? 0,
                ],
                'created_at' => $order->created_at->toISOString(),
            ],
        ]);
    }
}
