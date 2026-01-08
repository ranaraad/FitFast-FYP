<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AIRecommendationController extends Controller
{
    public function size(Request $request, string $user): JsonResponse
    {
        $resolvedUser = $this->resolveUser($request, $user);
        $this->syncUser($resolvedUser);

        $validated = $request->validate([
            'itemId' => ['nullable'],
            'garmentType' => ['nullable', 'string'],
        ]);

        $garmentType = Arr::get($validated, 'garmentType');
        $itemId = Arr::get($validated, 'itemId');

        if (!$garmentType && $itemId) {
            $garmentType = optional(Item::find($itemId))->garment_type;
        }

        if (!$garmentType) {
            throw ValidationException::withMessages([
                'garmentType' => 'A garment type is required for sizing.',
            ]);
        }

        try {
            $response = $this->http()->post(
                "/api/users/{$resolvedUser->id}/size",
                [
                    'garmentType' => $garmentType,
                    'itemId' => $itemId,
                ]
            );

            $response->throw();

            return response()->json($response->json());
        } catch (RequestException $exception) {
            return $this->errorResponse($exception);
        }
    }

    public function outfit(Request $request, string $user): JsonResponse
    {
        $resolvedUser = $this->resolveUser($request, $user);
        $this->syncUser($resolvedUser);

        $validated = $request->validate([
            'startingItemId' => ['nullable'],
            'style' => ['nullable', 'string'],
            'maxItems' => ['nullable', 'integer', 'between:2,6'],
        ]);

        try {
            $response = $this->http()->post(
                "/api/users/{$resolvedUser->id}/outfit",
                array_filter([
                    'startingItemId' => Arr::get($validated, 'startingItemId'),
                    'style' => Arr::get($validated, 'style'),
                    'maxItems' => Arr::get($validated, 'maxItems', 4),
                ], static fn ($value) => $value !== null)
            );

            $response->throw();

            return response()->json($response->json());
        } catch (RequestException $exception) {
            return $this->errorResponse($exception);
        }
    }

    public function recommendations(Request $request, string $user): JsonResponse
    {
        $resolvedUser = $this->resolveUser($request, $user);
        $this->syncUser($resolvedUser);

        $limit = (int) $request->input('limit', 6);

        try {
            $response = $this->http()->post(
                "/api/users/{$resolvedUser->id}/recommendations",
                ['limit' => max(1, min(20, $limit))]
            );

            $response->throw();

            return response()->json($response->json());
        } catch (RequestException $exception) {
            return $this->errorResponse($exception);
        }
    }

    public function sync(Request $request, string $user): JsonResponse
    {
        $resolvedUser = $this->resolveUser($request, $user);

        try {
            $profile = $this->syncUser($resolvedUser);
            return response()->json(['data' => $profile]);
        } catch (RequestException $exception) {
            return $this->errorResponse($exception);
        }
    }

    private function syncUser(User $user): array
    {
        $payload = [
            'user_id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'measurements' => $user->measurements ?? [],
            'preferences' => [],
            'purchase_history' => $this->purchaseHistory($user),
            'wishlist' => [],
            'view_history' => [],
        ];

        $response = $this->http()->post('/api/users', $payload);
        $response->throw();

        return $response->json('data', []);
    }

    private function resolveUser(Request $request, string $user): User
    {
        if ($user === 'me') {
            $authUser = $request->user() ?? Auth::user();
            if ($authUser instanceof User) {
                return $authUser;
            }

            throw ValidationException::withMessages([
                'user' => 'Authentication required.',
            ]);
        }

        return User::findOrFail($user);
    }

    private function purchaseHistory(User $user): array
    {
        return $user->orders()
            ->with(['orderItems.item'])
            ->latest('created_at')
            ->take(10)
            ->get()
            ->flatMap(static function ($order) {
                return $order->orderItems->map(static function ($orderItem) use ($order) {
                    return array_filter([
                        'item_id' => (string) $orderItem->item_id,
                        'item_name' => $orderItem->item->name ?? null,
                        'price' => $orderItem->unit_price ? (float) $orderItem->unit_price : null,
                        'purchased_at' => optional($order->created_at)->toAtomString(),
                        'selected_size' => $orderItem->selected_size,
                        'selected_color' => $orderItem->selected_color,
                        'quantity' => $orderItem->quantity,
                    ], static fn ($value) => $value !== null);
                });
            })
            ->values()
            ->all();
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.ai_service.base_url'))
            ->timeout(config('services.ai_service.timeout', 10))
            ->acceptJson();
    }

    private function errorResponse(RequestException $exception): JsonResponse
    {
        $status = $exception->response?->status() ?? 502;
        $message = $exception->response?->json('detail')
            ?? $exception->response?->json('message')
            ?? 'AI service unavailable.';

        return response()->json([
            'message' => $message,
        ], $status >= 400 ? $status : 502);
    }
}
