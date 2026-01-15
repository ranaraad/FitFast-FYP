<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\PaymentMethod;
use App\Models\Order;

class UserController extends Controller
{
    /**
     * Return authenticated user info.
     */
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'orders' => function ($query) {
                $query->latest()->with([
                    'store:id,name',
                    'orderItems.item.images',
                    'delivery',
                    'payment.paymentMethod',
                ]);
            },
            'paymentMethods',
        ]);

        $orders = $user->orders->map(function (Order $order) {
            $items = $order->orderItems->map(function ($orderItem) {
                $item = $orderItem->item;
                $primaryImage = $item && $item->primary_image ? $item->primary_image->image_path : null;

                return [
                    'id' => $item->id ?? $orderItem->id,
                    'name' => $item->name ?? 'Ordered item',
                    'quantity' => $orderItem->quantity,
                    'unit_price' => (float) $orderItem->unit_price,
                    'price' => (float) $orderItem->unit_price,
                    'total' => (float) ($orderItem->quantity * $orderItem->unit_price),
                    'size' => $orderItem->selected_size,
                    'color' => $orderItem->selected_color,
                    'image_url' => $primaryImage ? asset('storage/' . $primaryImage) : null,
                ];
            })->values()->toArray();

            $deliveryStatus = optional($order->delivery)->status;

            return [
                'id' => $order->id,
                'orderId' => $order->id,
                'code' => 'ORDER-' . $order->id,
                'status' => $order->status,
                'cmsStatus' => $deliveryStatus,
                'delivery_status' => $deliveryStatus,
                'user_id' => $order->user_id,
                'items_count' => $order->orderItems->sum('quantity'),
                'delivery' => [
                    'id' => optional($order->delivery)->id,
                    'status' => $deliveryStatus,
                    'address' => optional($order->delivery)->address,
                    'label' => optional($order->delivery)->carrier ?? 'Standard delivery',
                    'description' => optional($order->delivery)->tracking_id
                        ? 'Tracking ID: ' . optional($order->delivery)->tracking_id
                        : null,
                    'tracking_id' => optional($order->delivery)->tracking_id,
                    'carrier' => optional($order->delivery)->carrier,
                    'estimated_delivery' => optional(optional($order->delivery)->estimated_delivery)->toISOString(),
                ],
                'payment' => [
                    'status' => optional($order->payment)->status,
                    'amount' => optional($order->payment)->amount,
                    'method' => optional(optional($order->payment)->paymentMethod)->type,
                ],
                'store' => $order->store ? [
                    'id' => $order->store->id,
                    'name' => $order->store->name,
                ] : null,
                'items' => $items,
                'totals' => [
                    'total' => (float) $order->total_amount,
                    'subtotal' => (float) $order->total_amount,
                    'shipping' => 0.0,
                    'tax' => 0.0,
                    'discount' => 0.0,
                ],
                'eta' => optional(optional($order->delivery)->estimated_delivery)->toISOString(),
                'created_at' => optional($order->created_at)->toISOString(),
                'updated_at' => optional($order->updated_at)->toISOString(),
            ];
        })->values()->toArray();

        $paymentMethods = $user->paymentMethods->map(function (PaymentMethod $method) {
            $details = $method->details ?? [];

            return [
                'id' => $method->id,
                'type' => $method->type,
                'label' => $details['label'] ?? null,
                'brand' => $details['brand'] ?? null,
                'last4' => $details['last4'] ?? ($details['card_last_four'] ?? null),
                'exp_month' => $details['exp_month'] ?? $details['expiry_month'] ?? null,
                'exp_year' => $details['exp_year'] ?? $details['expiry_year'] ?? null,
                'is_default' => $method->is_default,
            ];
        })->values()->toArray();

        $userData = $user->toArray();
        $userData['orders'] = $orders;
        $userData['payment_methods'] = $paymentMethods;

        return response()->json($userData);
    }

    /**
     * Update authenticated user's measurements or basic info.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'measurements' => 'nullable|array',
            // Basic measurements (kept for backward compatibility)
            'measurements.height_cm' => 'nullable|numeric|min:100|max:250',
            'measurements.weight_kg' => 'nullable|numeric|min:30|max:200',
            'measurements.bust_cm' => 'nullable|numeric|min:50|max:150',
            'measurements.waist_cm' => 'nullable|numeric|min:40|max:150',
            'measurements.hips_cm' => 'nullable|numeric|min:50|max:200',
            'measurements.shoulder_width_cm' => 'nullable|numeric|min:30|max:70',
            'measurements.arm_length_cm' => 'nullable|numeric|min:40|max:80',
            'measurements.inseam_cm' => 'nullable|numeric|min:50|max:100',
            'measurements.body_shape' => 'nullable|string|max:50',
            'measurements.fit_preference' => 'nullable|string|max:50',
            
            // High Priority (10-16 garment types)
            'measurements.chest_circumference' => 'nullable|numeric|min:50|max:200',
            'measurements.waist_circumference' => 'nullable|numeric|min:40|max:200',
            'measurements.hips_circumference' => 'nullable|numeric|min:50|max:250',
            'measurements.garment_length' => 'nullable|numeric|min:30|max:150',
            'measurements.sleeve_length' => 'nullable|numeric|min:30|max:100',
            
            // Medium Priority (4-9 garment types)
            'measurements.inseam_length' => 'nullable|numeric|min:50|max:120',
            'measurements.thigh_circumference' => 'nullable|numeric|min:30|max:100',
            'measurements.leg_opening' => 'nullable|numeric|min:15|max:60',
            'measurements.dress_length' => 'nullable|numeric|min:50|max:200',
            'measurements.short_length' => 'nullable|numeric|min:10|max:80',
            'measurements.foot_length' => 'nullable|numeric|min:15|max:40',
            'measurements.shoulder_to_hem' => 'nullable|numeric|min:40|max:180',
            'measurements.skirt_length' => 'nullable|numeric|min:20|max:120',
            'measurements.foot_width' => 'nullable|numeric|min:5|max:20',
            
            // Low Priority (<4 garment types)
            'measurements.head_circumference' => 'nullable|numeric|min:40|max:80',
            'measurements.hood_height' => 'nullable|numeric|min:15|max:50',
            'measurements.bicep_circumference' => 'nullable|numeric|min:20|max:60',
            'measurements.collar_size' => 'nullable|numeric|min:30|max:60',
            'measurements.underbust_circumference' => 'nullable|numeric|min:50|max:150',
            'measurements.cup_size' => 'nullable|string|max:10',
            'measurements.rise' => 'nullable|numeric|min:15|max:50',
            'measurements.chain_length' => 'nullable|numeric|min:20|max:100',
            'measurements.bracelet_circumference' => 'nullable|numeric|min:10|max:30',
            
            // Additional measurements
            'measurements.back_width' => 'nullable|numeric|min:30|max:70',
            'measurements.neck_circumference' => 'nullable|numeric|min:25|max:60',
            
            'profile_photo' => 'nullable|image|max:5120',
            'remove_photo' => 'nullable|boolean',
        ]);

        $profilePhotoPath = $user->profile_photo_path;

        if ($request->boolean('remove_photo')) {
            if ($profilePhotoPath && Storage::disk('public')->exists($profilePhotoPath)) {
                Storage::disk('public')->delete($profilePhotoPath);
            }
            $profilePhotoPath = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($profilePhotoPath && Storage::disk('public')->exists($profilePhotoPath)) {
                Storage::disk('public')->delete($profilePhotoPath);
            }

            $profilePhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
            'measurements' => $validated['measurements'] ?? $user->measurements,
            'profile_photo_path' => $profilePhotoPath,
        ]);

        $user->refresh();

        return response()->json([
            'message' => 'User profile updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ], [], [
            'current_password' => 'current password',
            'new_password' => 'new password',
            'new_password_confirmation' => 'new password confirmation',
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
        ])->save();

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Delete the authenticated user's account.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        // Revoke all tokens (force logout)
        $user->tokens()->delete();
        // Delete the user account
        $user->delete();
        return response()->json([
            'message' => 'Account deleted successfully. You have been logged out and must register again to use the app.'
        ], 200);
    }
}
