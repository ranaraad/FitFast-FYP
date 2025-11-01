<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        Log::info("OrderObserver: Order #{$order->id} updated", [
            'old_status' => $order->getOriginal('status'),
            'new_status' => $order->status,
            'changes' => $order->getChanges()
        ]);

        // Check if we should create a delivery
        $this->createDeliveryIfReady($order);
    }

    /**
     * Check if order is ready for delivery creation and create it
     */
    private function createDeliveryIfReady(Order $order): void
    {
        // RELOAD THE ORDER WITH RELATIONSHIPS - THIS IS THE KEY FIX!
        $order->load(['payment', 'delivery', 'user']);

        Log::info("OrderObserver: Checking conditions for Order #{$order->id}", [
            'status' => $order->status,
            'has_payment' => !is_null($order->payment),
            'payment_status' => $order->payment ? $order->payment->status : 'no payment',
            'has_delivery' => !is_null($order->delivery),
            'has_user' => !is_null($order->user),
            'user_address' => $order->user ? $order->user->address : 'no user',
        ]);

        // Conditions for automatic delivery creation:
        $hasCompletedPayment = $order->payment && $order->payment->isCompleted();
        $isShippableStatus = in_array($order->status, [
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
            'confirmed',
            'processing'
        ]);
        $noExistingDelivery = !$order->delivery;
        $hasUserWithAddress = $order->user && $this->hasDeliveryAddress($order->user);

        Log::info("OrderObserver: Conditions for Order #{$order->id}", [
            'hasCompletedPayment' => $hasCompletedPayment,
            'isShippableStatus' => $isShippableStatus,
            'noExistingDelivery' => $noExistingDelivery,
            'hasUserWithAddress' => $hasUserWithAddress,
            'all_conditions_met' => $hasCompletedPayment && $isShippableStatus && $noExistingDelivery && $hasUserWithAddress,
        ]);

        if ($hasCompletedPayment && $isShippableStatus && $noExistingDelivery && $hasUserWithAddress) {
            Log::info("OrderObserver: ALL CONDITIONS MET - Creating delivery for Order #{$order->id}");
            $this->createDelivery($order);
        } else {
            Log::info("OrderObserver: Conditions NOT met for Order #{$order->id}");
        }
    }

    /**
     * Check if user has delivery address information
     */
    private function hasDeliveryAddress($user): bool
    {
        return !empty($user->address) ||
               !empty($user->shipping_address) ||
               !empty($user->default_address);
    }

    /**
     * Create delivery record for order
     */
    private function createDelivery(Order $order): void
    {
        try {
            $deliveryAddress = $this->getDeliveryAddress($order->user);

            // FIX: Use Carbon to properly format the timestamp
            $estimatedDelivery = Carbon::now()->addDays(3);

            $delivery = Delivery::create([
                'order_id' => $order->id,
                'address' => $deliveryAddress,
                'status' => 'pending',
                'carrier' => null,
                'tracking_id' => null,
                'estimated_delivery' => $estimatedDelivery,
            ]);

            Log::info("OrderObserver: SUCCESS - Created delivery #{$delivery->id} for Order #{$order->id}", [
                'delivery_id' => $delivery->id,
                'address' => $deliveryAddress,
                'estimated_delivery' => $estimatedDelivery->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error("OrderObserver: FAILED to create delivery for Order #{$order->id}: " . $e->getMessage());
        }
    }

    /**
     * Get delivery address from user
     */
    private function getDeliveryAddress($user): string
    {
        if (!empty($user->shipping_address)) {
            return $user->shipping_address;
        }

        if (!empty($user->address)) {
            return $user->address;
        }

        return "Address not provided for user: {$user->name} ({$user->email})";
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // Optional: Delete associated delivery if order is deleted
        if ($order->delivery) {
            $order->delivery->delete();
        }
    }
}
