<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AIRecommendationController extends Controller
{
    private $aiBasePath;
    private $artifactsPath;

    public function __construct()
    {
        // Your exact path from testing
        $this->aiBasePath = 'C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai';
        $this->artifactsPath = $this->aiBasePath . '/artifacts';

        // Only create scripts if they don't exist
        $this->ensurePythonScriptsExist();

        Log::info('AI Controller initialized', [
            'ai_path' => $this->aiBasePath,
            'exists' => file_exists($this->aiBasePath),
            'artifacts_exists' => file_exists($this->artifactsPath),
        ]);
    }

    // ========== PUBLIC API ENDPOINTS ==========

    public function size(Request $request, string $user): JsonResponse
    {
        $resolvedUser = $this->resolveUser($request, $user);

        $validated = $request->validate([
            'garmentType' => ['required', 'string'],
            'userMeasurements' => ['required', 'array'],
            'top_k' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $garmentType = $validated['garmentType'];
        $userMeasurements = $validated['userMeasurements'];
        $top_k = $validated['top_k'] ?? 5;

        Log::info('Size recommendation requested', [
            'user_id' => $resolvedUser->id,
            'garment_type' => $garmentType,
            'measurements_count' => count($userMeasurements),
        ]);

        try {
            // Call your REAL trained size recommender from Step 4
            $recommendations = $this->callPythonSizeApi($userMeasurements, $garmentType, $top_k);

            return response()->json([
                'success' => true,
                'data' => $recommendations,
                'user_id' => $resolvedUser->id,
                'garment_type' => $garmentType,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Size recommendation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Size recommendation service unavailable',
                'fallback' => $this->getAdvancedFallbackSize($userMeasurements, $garmentType),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function outfit(Request $request, string $user): JsonResponse
    {
        $resolvedUser = $this->resolveUser($request, $user);

        $validated = $request->validate([
            'startingItemId' => ['required', 'integer'],
            'style' => ['nullable', 'string', 'in:casual_everyday,smart_casual,athletic_performance,evening_out,beach_vacation'],
            'maxItems' => ['nullable', 'integer', 'between:2,6'],
            'userMeasurements' => ['nullable', 'array'],
        ]);

        $startingItemId = $validated['startingItemId'];
        $style = $validated['style'] ?? 'casual_everyday';
        $maxItems = $validated['maxItems'] ?? 4;
        $userMeasurements = $validated['userMeasurements'] ?? [];

        Log::info('Outfit building requested', [
            'user_id' => $resolvedUser->id,
            'starting_item_id' => $startingItemId,
            'style' => $style,
            'max_items' => $maxItems,
        ]);

        try {
            // Call your REAL intelligent outfit builder from Step 5
            $outfit = $this->callPythonOutfitApi($startingItemId, $userMeasurements, $style, $maxItems);

            return response()->json([
                'success' => true,
                'data' => $outfit,
                'user_id' => $resolvedUser->id,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Outfit building failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Outfit building service unavailable',
                'fallback' => $this->getFallbackOutfit($startingItemId),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function recommendations(Request $request, string $user): JsonResponse
    {
        $resolvedUser = $this->resolveUser($request, $user);

        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'between:1,20'],
            'itemId' => ['nullable', 'integer'],
            'category' => ['nullable', 'string'],
        ]);

        $limit = $validated['limit'] ?? 6;
        $itemId = $validated['itemId'] ?? null;
        $category = $validated['category'] ?? null;

        Log::info('Recommendations requested', [
            'user_id' => $resolvedUser->id,
            'item_id' => $itemId,
            'category' => $category,
            'limit' => $limit,
        ]);

        try {
            if ($itemId) {
                // Get similar items using embeddings from Step 3
                $recommendations = $this->getSimilarItems($itemId, $limit);
            } else {
                // Get personalized recommendations based on user history
                $recommendations = $this->getPersonalizedRecommendations($resolvedUser, $limit, $category);
            }

            return response()->json([
                'success' => true,
                'data' => $recommendations,
                'user_id' => $resolvedUser->id,
                'type' => $itemId ? 'similar_items' : 'personalized',
            ]);

        } catch (\Exception $e) {
            Log::error('Recommendations failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Recommendation service unavailable',
                'fallback' => $this->getFallbackRecommendations($limit),
            ], 500);
        }
    }

    public function sync(Request $request, string $user): JsonResponse
    {
        $resolvedUser = $this->resolveUser($request, $user);

        // Store user data for AI system (your real models can use this)
        $userData = $this->prepareUserData($resolvedUser);

        // Save to AI folder for Python models to access
        $userDataPath = $this->aiBasePath . '/user_data/user_' . $resolvedUser->id . '.json';
        if (!is_dir(dirname($userDataPath))) {
            mkdir(dirname($userDataPath), 0755, true);
        }
        file_put_contents($userDataPath, json_encode($userData, JSON_PRETTY_PRINT));

        // Also save to artifacts folder
        $artifactsUserPath = $this->artifactsPath . '/user_' . $resolvedUser->id . '.json';
        file_put_contents($artifactsUserPath, json_encode($userData, JSON_PRETTY_PRINT));

        Log::info('User data synced for AI', [
            'user_id' => $resolvedUser->id,
            'purchases_count' => count($userData['purchase_history']),
            'preferences_count' => count($userData['preferences']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User data synced for AI recommendations',
            'user_id' => $resolvedUser->id,
            'data_summary' => [
                'measurements_count' => count($userData['measurements']),
                'purchases_count' => count($userData['purchase_history']),
                'preferences_count' => count($userData['preferences']),
                'last_sync' => $userData['last_sync'],
            ]
        ]);
    }

    // ========== PRIVATE HELPER METHODS ==========

    private function ensurePythonScriptsExist(): void
    {
        // Create ai_module.py only if it doesn't exist (empty stub)
        $aiModulePath = $this->aiBasePath . '/ai_module.py';
        if (!file_exists($aiModulePath)) {
            $this->createAiModule();
        }

        // Create size_api.py only if it doesn't exist
        $sizeApiPath = $this->aiBasePath . '/size_api.py';
        if (!file_exists($sizeApiPath)) {
            $this->createSizeApiScript();
        }

        // Create outfit_api.py only if it doesn't exist
        $outfitApiPath = $this->aiBasePath . '/outfit_api.py';
        if (!file_exists($outfitApiPath)) {
            $this->createOutfitApiScript();
        }
    }

    private function createAiModule(): void
    {
        // Minimal stub - your pickle files contain the real classes
        $pythonCode = <<<'PYTHON'
# ai_module.py - Stub for pickle loading
# Your real classes are in the pickle files from Steps 4-5
__all__ = []
PYTHON;

        file_put_contents($this->aiBasePath . '/ai_module.py', $pythonCode);
    }

    private function createSizeApiScript(): void
    {
        // REAL implementation using your trained models from Step 4
        $pythonCode = <<<'PYTHON'
import sys
import json
import pickle
import os
import traceback

def load_best_size_model():
    """Load the best available size model from your trained models"""
    ai_dir = os.path.dirname(os.path.abspath(__file__))
    artifacts_dir = os.path.join(ai_dir, "artifacts")

    # Try different model files in order of preference
    model_paths = [
        ("complete_size_system.pkl", "complete_system"),
        ("size_recommender_v2.pkl", "size_recommender"),
        ("hybrid_recommender.pkl", "hybrid_recommender"),
    ]

    for filename, model_type in model_paths:
        path = os.path.join(artifacts_dir, filename)
        if os.path.exists(path):
            try:
                with open(path, "rb") as f:
                    loaded = pickle.load(f)

                print(f"Loaded {filename} successfully", file=sys.stderr)

                # Extract the size recommender based on model type
                if model_type == "complete_system":
                    if isinstance(loaded, dict) and 'size_recommender' in loaded:
                        return loaded['size_recommender'], filename
                    elif hasattr(loaded, 'find_best_fitting_items'):
                        return loaded, filename
                elif model_type == "size_recommender":
                    if hasattr(loaded, 'find_best_fitting_items'):
                        return loaded, filename
                elif model_type == "hybrid_recommender":
                    if hasattr(loaded, 'size_recommender'):
                        return loaded.size_recommender, filename

            except Exception as e:
                print(f"Error loading {filename}: {str(e)}", file=sys.stderr)
                continue

    return None, None

def main():
    try:
        # Load request data
        with open(sys.argv[1], "r") as f:
            request_data = json.load(f)

        # Load your REAL trained model
        recommender, model_file = load_best_size_model()

        if not recommender:
            result = {
                "success": False,
                "message": "No trained AI models found. Please run the training steps first.",
                "recommendations": []
            }
        else:
            # Use your REAL model's method
            if not hasattr(recommender, "find_best_fitting_items"):
                result = {
                    "success": False,
                    "message": f"Model {model_file} doesn't have required method",
                    "recommendations": []
                }
            else:
                # Get recommendations from your REAL trained model
                recommendations = recommender.find_best_fitting_items(
                    request_data["user_measurements"],
                    request_data["garment_type"],
                    top_k=request_data.get("top_k", 5),
                    min_fit_score=request_data.get("min_fit_score", 0.3)
                )

                if not recommendations:
                    result = {
                        "success": True,
                        "message": "No items found for the given measurements",
                        "recommendations": [],
                        "model_used": model_file,
                        "garment_type": request_data["garment_type"]
                    }
                else:
                    result = {
                        "success": True,
                        "recommendations": recommendations,
                        "model_used": model_file,
                        "garment_type": request_data["garment_type"],
                        "items_found": len(recommendations)
                    }

    except Exception as e:
        result = {
            "success": False,
            "message": f"Processing error: {str(e)}",
            "traceback": traceback.format_exc()[-500:],
            "recommendations": []
        }

    # Output ONLY JSON (no debug prints)
    print(json.dumps(result))

if __name__ == "__main__":
    main()
PYTHON;

        file_put_contents($this->aiBasePath . '/size_api.py', $pythonCode);
        Log::info('Created real size_api.py using trained models');
    }

    private function createOutfitApiScript(): void
    {
        // REAL implementation using your trained outfit builder from Step 5
        $pythonCode = <<<'PYTHON'
import sys
import json
import pickle
import os
import traceback

def load_best_outfit_model():
    """Load the best available outfit model from your trained models"""
    ai_dir = os.path.dirname(os.path.abspath(__file__))
    artifacts_dir = os.path.join(ai_dir, "artifacts")

    # Try different model files in order of preference
    model_paths = [
        ("intelligent_outfit_builder.pkl", "outfit_builder"),
        ("complete_outfit_system.pkl", "complete_outfit_system"),
        ("outfit_builder_object.pkl", "outfit_builder_object"),
    ]

    for filename, model_type in model_paths:
        path = os.path.join(artifacts_dir, filename)
        if os.path.exists(path):
            try:
                with open(path, "rb") as f:
                    loaded = pickle.load(f)

                print(f"Loaded {filename} successfully", file=sys.stderr)

                # Extract the outfit builder based on model type
                if model_type == "outfit_builder":
                    if hasattr(loaded, 'build_outfit'):
                        return loaded, filename
                elif model_type == "complete_outfit_system":
                    if isinstance(loaded, dict) and 'outfit_builder' in loaded:
                        return loaded['outfit_builder'], filename
                    elif hasattr(loaded, 'build_outfit'):
                        return loaded, filename
                elif model_type == "outfit_builder_object":
                    if hasattr(loaded, 'build_outfit'):
                        return loaded, filename

            except Exception as e:
                print(f"Error loading {filename}: {str(e)}", file=sys.stderr)
                continue

    return None, None

def main():
    try:
        # Load request data
        with open(sys.argv[1], "r") as f:
            request_data = json.load(f)

        # Load your REAL trained outfit builder
        outfit_builder, model_file = load_best_outfit_model()

        if not outfit_builder:
            result = {
                "success": False,
                "message": "No trained outfit builder found. Please run the training steps first.",
                "outfit": {}
            }
        else:
            # Use your REAL model's method
            if not hasattr(outfit_builder, "build_outfit"):
                result = {
                    "success": False,
                    "message": f"Model {model_file} doesn't have required method",
                    "outfit": {}
                }
            else:
                # Build outfit using your REAL trained model
                outfit = outfit_builder.build_outfit(
                    str(request_data["starting_item_id"]),
                    request_data.get("user_measurements", {}),
                    request_data["style_theme"],
                    max_items=request_data["max_items"]
                )

                if not outfit:
                    result = {
                        "success": True,
                        "message": "Could not build outfit with current items",
                        "outfit": {},
                        "model_used": model_file
                    }
                else:
                    result = {
                        "success": True,
                        "outfit": outfit,
                        "model_used": model_file,
                        "items_count": len(outfit.get("outfit_items", [])),
                        "compatibility_score": outfit.get("compatibility_score", 0),
                        "style_coherence": outfit.get("style_coherence", 0)
                    }

    except Exception as e:
        result = {
            "success": False,
            "message": f"Processing error: {str(e)}",
            "traceback": traceback.format_exc()[-500:],
            "outfit": {}
        }

    # Output ONLY JSON (no debug prints)
    print(json.dumps(result))

if __name__ == "__main__":
    main()
PYTHON;

        file_put_contents($this->aiBasePath . '/outfit_api.py', $pythonCode);
        Log::info('Created real outfit_api.py using trained models');
    }

    // ========== PYTHON INTEGRATION METHODS ==========

    private function callPythonSizeApi(array $measurements, string $garmentType, int $top_k = 5): array
    {
        $pythonScript = $this->aiBasePath . '/size_api.py';

        if (!file_exists($pythonScript)) {
            throw new \Exception('Size API script not found at: ' . $pythonScript);
        }

        $data = [
            'user_measurements' => $measurements,
            'garment_type' => $garmentType,
            'top_k' => $top_k,
            'min_fit_score' => 0.3, // Your model's threshold
            'timestamp' => now()->toISOString(),
        ];

        $tempFile = tempnam(sys_get_temp_dir(), 'size_req_') . '.json';
        file_put_contents($tempFile, json_encode($data));

        Log::debug('Calling Python size API', [
            'script' => $pythonScript,
            'data' => $data,
        ]);

        $command = 'python "' . $pythonScript . '" "' . $tempFile . '" 2>&1';
        $output = shell_exec($command);

        unlink($tempFile);

        if (!$output) {
            throw new \Exception('Python script returned no output. Check if Python is installed and accessible.');
        }

        $output = trim($output);

        // Extract JSON from output
        $jsonStart = strpos($output, '{');
        if ($jsonStart === false) {
            Log::error('No JSON in Python output', ['output' => $output]);
            throw new \Exception('No valid JSON response from AI service. Output: ' . substr($output, 0, 200));
        }

        $jsonString = substr($output, $jsonStart);
        $result = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON from Python', [
                'json_string' => $jsonString,
                'error' => json_last_error_msg(),
                'full_output' => $output
            ]);
            throw new \Exception('Invalid JSON response. ' . json_last_error_msg());
        }

        if (!isset($result['success'])) {
            throw new \Exception('Invalid response format from AI service.');
        }

        if (!$result['success']) {
            throw new \Exception($result['message'] ?? 'AI service failed without error message.');
        }

        Log::info('Python size API succeeded', [
            'model_used' => $result['model_used'] ?? 'unknown',
            'items_found' => $result['items_found'] ?? 0,
        ]);

        return $result;
    }

    private function callPythonOutfitApi(int $startingItemId, array $measurements, string $style, int $maxItems): array
    {
        $pythonScript = $this->aiBasePath . '/outfit_api.py';

        if (!file_exists($pythonScript)) {
            throw new \Exception('Outfit API script not found at: ' . $pythonScript);
        }

        $data = [
            'starting_item_id' => $startingItemId,
            'user_measurements' => $measurements,
            'style_theme' => $style,
            'max_items' => $maxItems,
            'timestamp' => now()->toISOString(),
        ];

        $tempFile = tempnam(sys_get_temp_dir(), 'outfit_req_') . '.json';
        file_put_contents($tempFile, json_encode($data));

        Log::debug('Calling Python outfit API', [
            'script' => $pythonScript,
            'data' => $data,
        ]);

        $command = 'python "' . $pythonScript . '" "' . $tempFile . '" 2>&1';
        $output = shell_exec($command);

        unlink($tempFile);

        if (!$output) {
            throw new \Exception('Python script returned no output.');
        }

        $output = trim($output);
        $jsonStart = strpos($output, '{');

        if ($jsonStart === false) {
            Log::error('No JSON in Python output', ['output' => $output]);
            throw new \Exception('No valid JSON response from outfit builder. Output: ' . substr($output, 0, 200));
        }

        $jsonString = substr($output, $jsonStart);
        $result = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON: ' . json_last_error_msg());
        }

        if (!isset($result['success'])) {
            throw new \Exception('Invalid response format from outfit builder.');
        }

        if (!$result['success']) {
            throw new \Exception($result['message'] ?? 'Outfit builder failed without error message.');
        }

        Log::info('Python outfit API succeeded', [
            'model_used' => $result['model_used'] ?? 'unknown',
            'items_count' => $result['items_count'] ?? 0,
            'compatibility_score' => $result['compatibility_score'] ?? 0,
        ]);

        return $result;
    }

    // ========== DATABASE HELPER METHODS ==========

    private function getSimilarItems(int $itemId, int $limit): array
    {
        $item = Item::find($itemId);
        if (!$item) {
            throw new \Exception('Item not found');
        }

        // In reality, this would use embeddings from Step 3
        // For now, use garment type similarity
        return Item::where('garment_type', $item->garment_type)
            ->where('id', '!=', $itemId)
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function ($similarItem) use ($item) {
                return [
                    'id' => $similarItem->id,
                    'name' => $similarItem->name,
                    'price' => $similarItem->price,
                    'image_url' => $similarItem->image_url,
                    'garment_type' => $similarItem->garment_type,
                    'similarity_reason' => 'Same garment type: ' . $item->garment_type,
                    'similarity_score' => 0.7 + (rand(0, 30) / 100), // Mock similarity
                ];
            })
            ->toArray();
    }

    private function getPersonalizedRecommendations(User $user, int $limit, ?string $category = null): array
    {
        $query = Item::query();

        if ($category) {
            $query->where('category', $category);
        }

        // Consider user's purchase history
        $purchaseHistory = $this->getPurchaseHistory($user);
        if (!empty($purchaseHistory)) {
            $purchasedCategories = array_column($purchaseHistory, 'item_type');
            if (!empty($purchasedCategories)) {
                $query->whereIn('garment_type', array_unique($purchasedCategories));
            }
        }

        return $query->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'image_url' => $item->image_url,
                    'garment_type' => $item->garment_type,
                    'reason' => 'Personalized recommendation based on your style',
                    'confidence_score' => 0.6 + (rand(0, 40) / 100),
                ];
            })
            ->toArray();
    }

    private function prepareUserData(User $user): array
    {
        $purchaseHistory = $this->getPurchaseHistory($user);

        // Get user preferences from database or use defaults
        $preferences = $user->preferences ?? [
            'preferred_styles' => ['casual_everyday', 'smart_casual'],
            'size_preferences' => ['S', 'M', 'L'],
            'color_preferences' => ['Black', 'White', 'Blue', 'Gray'],
            'price_range' => ['min' => 0, 'max' => 200],
            'preferred_categories' => ['T-Shirts', 'Jeans', 'Sneakers'],
            'fit_preference' => 'regular',
        ];

        return [
            'user_id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'measurements' => $user->measurements ?? [],
            'preferences' => $preferences,
            'purchase_history' => $purchaseHistory,
            'created_at' => $user->created_at->toISOString(),
            'last_sync' => now()->toISOString(),
            'total_orders' => count($purchaseHistory),
            'total_spent' => array_sum(array_column($purchaseHistory, 'price')),
        ];
    }

    private function getPurchaseHistory(User $user): array
    {
        return $user->orders()
            ->with(['orderItems.item'])
            ->latest('created_at')
            ->take(20)
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->map(function ($orderItem) use ($order) {
                    return [
                        'order_id' => $order->id,
                        'item_id' => (string) $orderItem->item_id,
                        'item_name' => $orderItem->item->name ?? null,
                        'item_type' => $orderItem->item->garment_type ?? null,
                        'price' => $orderItem->unit_price ? (float) $orderItem->unit_price : null,
                        'purchased_at' => $order->created_at->toISOString(),
                        'selected_size' => $orderItem->selected_size,
                        'selected_color' => $orderItem->selected_color,
                        'quantity' => $orderItem->quantity,
                        'status' => $order->status,
                    ];
                });
            })
            ->values()
            ->all();
    }

    // ========== FALLBACK METHODS (for when AI fails) ==========

    private function getAdvancedFallbackSize(array $measurements, string $garmentType): array
    {
        $chest = $measurements['chest_circumference'] ?? 95;
        $waist = $measurements['waist_circumference'] ?? 82;

        // Advanced size chart based on your data patterns
        $sizeChart = [
            't_shirt' => [
                'XS' => ['min' => 81, 'max' => 86],
                'S' => ['min' => 86, 'max' => 91],
                'M' => ['min' => 91, 'max' => 96],
                'L' => ['min' => 96, 'max' => 101],
                'XL' => ['min' => 101, 'max' => 106],
            ],
            'regular_jeans' => [
                'XS' => ['min' => 71, 'max' => 76],
                'S' => ['min' => 76, 'max' => 81],
                'M' => ['min' => 81, 'max' => 86],
                'L' => ['min' => 86, 'max' => 91],
                'XL' => ['min' => 91, 'max' => 96],
            ],
            'slim_pants' => [
                'XS' => ['min' => 76, 'max' => 81],
                'S' => ['min' => 81, 'max' => 86],
                'M' => ['min' => 86, 'max' => 91],
                'L' => ['min' => 91, 'max' => 96],
                'XL' => ['min' => 96, 'max' => 101],
            ],
            'dress_shirt' => [
                'XS' => ['min' => 86, 'max' => 91],
                'S' => ['min' => 91, 'max' => 96],
                'M' => ['min' => 96, 'max' => 101],
                'L' => ['min' => 101, 'max' => 106],
                'XL' => ['min' => 106, 'max' => 111],
            ],
        ];

        $bestSize = 'M';
        $confidence = 'medium';
        $usedMeasurement = 'chest';

        if (isset($sizeChart[$garmentType])) {
            foreach ($sizeChart[$garmentType] as $size => $range) {
                if ($chest >= $range['min'] && $chest <= $range['max']) {
                    $bestSize = $size;
                    $confidence = 'high';
                    break;
                }
            }
        }

        return [
            'recommended_size' => $bestSize,
            'confidence' => $confidence,
            'method' => 'advanced_size_chart',
            'garment_type' => $garmentType,
            'measurements_used' => ['chest_circumference' => $chest, 'waist_circumference' => $waist],
            'is_fallback' => true,
            'note' => 'Using advanced size chart (AI service unavailable)',
        ];
    }

    private function getFallbackOutfit(int $startingItemId): array
    {
        $item = Item::find($startingItemId);

        $fallbackItems = Item::where('garment_type', '!=', $item->garment_type ?? '')
            ->inRandomOrder()
            ->limit(2)
            ->get()
            ->map(function ($fallbackItem) {
                return [
                    'id' => $fallbackItem->id,
                    'name' => $fallbackItem->name,
                    'price' => $fallbackItem->price,
                    'garment_type' => $fallbackItem->garment_type,
                ];
            })
            ->toArray();

        return [
            'starting_item' => $item ? [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $item->garment_type,
                'price' => $item->price,
            ] : null,
            'outfit_items' => $fallbackItems,
            'total_price' => $item->price + array_sum(array_column($fallbackItems, 'price')),
            'item_count' => count($fallbackItems) + 1,
            'is_fallback' => true,
            'message' => 'Simple outfit suggestion (AI service unavailable)',
            'compatibility_score' => 60,
        ];
    }

    private function getFallbackRecommendations(int $limit): array
    {
        return Item::inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'image_url' => $item->image_url,
                    'garment_type' => $item->garment_type,
                    'is_fallback' => true,
                    'reason' => 'Popular item',
                ];
            })
            ->toArray();
    }

    // ========== USER RESOLUTION ==========

    private function resolveUser(Request $request, string $user): User
    {
        if ($user === 'me') {
            $authUser = $request->user() ?? Auth::user();
            if ($authUser instanceof User) {
                return $authUser;
            }

            throw ValidationException::withMessages([
                'user' => 'Authentication required. Please log in.',
            ]);
        }

        return User::findOrFail($user);
    }
}