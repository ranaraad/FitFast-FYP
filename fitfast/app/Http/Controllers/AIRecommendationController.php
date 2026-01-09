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
use Illuminate\Support\Str;
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

            $result = $response->json();

            if (is_array($result) && isset($result['data']) && is_array($result['data'])) {
                $result['data'] = $this->normalizeSizePayload($result['data'], $garmentType);
            }

            return response()->json($result);
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

        $outfitPayload = array_filter([
            'startingItemId' => Arr::get($validated, 'startingItemId'),
            'style' => Arr::get($validated, 'style'),
            'maxItems' => Arr::get($validated, 'maxItems', 4),
        ], static fn ($value) => $value !== null);

        logger()->info('Dispatching AI outfit request', [
            'user_id' => $resolvedUser->id,
            'payload' => $outfitPayload,
        ]);

        try {
            $response = $this->http()->post(
                "/api/users/{$resolvedUser->id}/outfit",
                $outfitPayload
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

    private function normalizeSizePayload(array $payload, ?string $garmentType): array
    {
        $payload = $this->normalizeSizeEntry($payload, $garmentType);

        if (isset($payload['recommendations']) && is_array($payload['recommendations'])) {
            $payload['recommendations'] = array_map(function ($entry) use ($garmentType) {
                return is_array($entry) ? $this->normalizeSizeEntry($entry, $garmentType) : $entry;
            }, $payload['recommendations']);
        }

        return $payload;
    }

    private function normalizeSizeEntry(array $entry, ?string $garmentType): array
    {
        $sizeValue = null;
        foreach (['recommended_size', 'recommendedSize', 'size', 'suggested_size', 'suggestion'] as $key) {
            $candidate = Arr::get($entry, $key);
            if ($candidate !== null && $candidate !== '') {
                $sizeValue = $candidate;
                break;
            }
        }

        $normalized = $this->normalizeSizeValue($sizeValue, $garmentType);

        if ($normalized !== null) {
            $entry['recommended_size'] = $normalized;
            $entry['recommendedSize'] = $entry['recommendedSize'] ?? $normalized;
            $entry['size'] = $normalized;
            $entry['size_label'] = $entry['size_label'] ?? $normalized;
        }

        return $entry;
    }

    private function normalizeSizeValue(mixed $value, ?string $garmentType): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = Arr::first($value, static fn ($entry) => $entry !== null && $entry !== '');
        }

        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return null;
        }

        if ($this->shouldForceAlphaSize($garmentType) && $this->isNumericSize($stringValue)) {
            return $this->mapNumericSizeToAlpha((float) $stringValue);
        }

        return $this->standardizeAlphaSize($stringValue);
    }

    private function shouldForceAlphaSize(?string $garmentType): bool
    {
        if (!$garmentType) {
            return false;
        }

        $normalized = Str::lower(str_replace(['_', '-'], ' ', $garmentType));

        if ($this->shouldKeepNumericSizes($normalized)) {
            return false;
        }

        $clothingKeywords = [
            'shirt',
            'top',
            'tee',
            'hoodie',
            'sweater',
            'jumper',
            'jacket',
            'coat',
            'polo',
            'dress',
            'skirt',
            'pant',
            'jean',
            'trouser',
            'short',
            'legging',
            'sweatpant',
            'sweatshirt',
            'blazer',
            'suit',
            'romper',
            'bodysuit',
            'onesie',
            'outerwear',
            'activewear',
            'uniform',
            'vest',
            'cardigan',
            'tunic',
            'kimono',
            'overall',
            'pullover',
        ];

        foreach ($clothingKeywords as $keyword) {
            if (Str::contains($normalized, $keyword)) {
                return true;
            }
        }

        return true;
    }

    private function shouldKeepNumericSizes(string $normalized): bool
    {
        $nonClothingKeywords = [
            'shoe',
            'sneaker',
            'boot',
            'heel',
            'sandal',
            'flip flop',
            'slipper',
            'loafer',
            'moccasin',
            'cleat',
            'wedge',
            'stiletto',
            'oxford',
            'jewelry',
            'necklace',
            'bracelet',
            'ring',
            'earring',
            'watch',
            'chain',
            'accessory',
            'belt',
            'bag',
            'purse',
            'wallet',
            'luggage',
            'backpack',
            'hat',
            'cap',
            'beanie',
            'helmet',
            'glasses',
            'goggle',
            'scarf',
            'wrap',
            'shawl',
            'tie',
            'bowtie',
            'suspenders',
        ];

        foreach ($nonClothingKeywords as $keyword) {
            if (Str::contains($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function isNumericSize(string $value): bool
    {
        if (is_numeric($value)) {
            return true;
        }

        return (bool) preg_match('/^\d+(\.\d+)?$/', $value);
    }

    private function mapNumericSizeToAlpha(float $size): string
    {
        $rounded = (int) round($size);

        if ($rounded <= 0) {
            return 'XS';
        }

        if ($rounded <= 2) {
            return 'XS';
        }

        if ($rounded <= 4) {
            return 'S';
        }

        if ($rounded <= 8) {
            return 'M';
        }

        if ($rounded <= 12) {
            return 'L';
        }

        if ($rounded <= 16) {
            return 'XL';
        }

        if ($rounded <= 20) {
            return 'XXL';
        }

        $centimeters = $size > 60 ? (int) round($size) : (int) round($size * 2.54);

        return $centimeters > 0 ? $centimeters . ' cm' : (string) $rounded;
    }

    private function standardizeAlphaSize(string $value): string
    {
        $normalized = strtoupper(trim($value));
        $collapsed = preg_replace('/[^A-Z0-9]/', '', $normalized);

        $aliases = [
            'EXTRASMALL' => 'XS',
            'XSMALL' => 'XS',
            'SMALL' => 'S',
            'MEDIUM' => 'M',
            'MED' => 'M',
            'LARGE' => 'L',
            'XLARGE' => 'XL',
            'EXTRALARGE' => 'XL',
            'XXLARGE' => 'XXL',
            '2XL' => 'XXL',
            '3XL' => 'XXXL',
            '4XL' => 'XXXXL',
        ];

        if (isset($aliases[$collapsed])) {
            return $aliases[$collapsed];
        }

        if (preg_match('/^X{1,4}L$/', $normalized)) {
            return $normalized;
        }

        if (in_array($normalized, ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'XXXXL'], true)) {
            return $normalized;
        }

        if (preg_match('/^(\d+)\s*CM$/', $normalized, $matches)) {
            return $matches[1] . ' cm';
        }

        return $normalized;
    }
}
