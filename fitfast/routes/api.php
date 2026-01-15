
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\UserController;
use App\Http\Controllers\Client\StoreController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\API\SupportChatController;
use App\Http\Controllers\AIRecommendationController; 

// ========== OUTFIT PYTHON STORE_ID TEST ROUTE ==========
use Illuminate\Http\Request;
Route::post('/test-outfit-store-ids', function (Request $request) {
    $validated = $request->validate([
        'startingItemId' => 'required|integer',
    ]);
    
    $controller = new AIRecommendationController();
    
    // Manually call the Python script to see what it returns
    $aiBasePath = 'C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai';
    $pythonScript = $aiBasePath . '/outfit_api.py';
    
    $data = [
        'starting_item_id' => $validated['startingItemId'],
        'user_measurements' => [],
        'style_theme' => 'casual_everyday',
        'max_items' => 3,
        'timestamp' => now()->toISOString(),
    ];
    
    $tempFile = tempnam(sys_get_temp_dir(), 'outfit_test_') . '.json';
    file_put_contents($tempFile, json_encode($data));
    
    $command = 'python "' . $pythonScript . '" "' . $tempFile . '" 2>&1';
    $output = shell_exec($command);
    
    unlink($tempFile);
    
    // Parse the Python output
    $output = trim($output);
    $jsonStart = strpos($output, '{');
    
    if ($jsonStart !== false) {
        $jsonString = substr($output, $jsonStart);
        $result = json_decode($jsonString, true);
    } else {
        $result = ['raw_output' => $output];
    }
    
    return response()->json([
        'python_output' => $output,
        'parsed_result' => $result,
        'data_sent' => $data,
        'first_item_store_id' => $result['outfit']['outfit_items'][0]['store_id'] ?? 'NOT FOUND',
        'all_items' => $result['outfit']['outfit_items'] ?? [],
    ]);
});

// ========== PUBLIC ROUTES ==========
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{store}', [StoreController::class, 'show']);

// ========== DATABASE VERIFICATION ROUTES ==========
Route::prefix('db-test')->group(function () {
    // Test 1: Check all items in database
    Route::get('/all-items', function () {
        $items = \App\Models\Item::all();

        return response()->json([
            'total_items' => $items->count(),
            'sample_items' => $items->take(10)->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'garment_type' => $item->garment_type,
                    'price' => $item->price,
                    'category' => $item->category,
                    'store' => $item->store ? $item->store->name : null,
                    'created_at' => $item->created_at,
                    'sizing_data_exists' => !empty($item->sizing_data),
                    'color_variants_exists' => !empty($item->color_variants),
                ];
            }),
            'garment_types' => \App\Models\Item::select('garment_type')
                ->distinct()
                ->pluck('garment_type')
                ->toArray(),
        ]);
    });

    // Test 2: Check specific items from AI test
    Route::get('/ai-test-items', function () {
        $aiItems = [1, 2, 3];

        $items = \App\Models\Item::whereIn('id', $aiItems)->get();

        if ($items->isEmpty()) {
            return response()->json([
                'error' => 'AI test items not found in database',
                'searched_ids' => $aiItems,
                'database_has_items' => \App\Models\Item::count(),
                'available_ids' => \App\Models\Item::pluck('id')->take(20)->toArray(),
            ]);
        }

        return response()->json([
            'ai_test_items_found' => $items->count(),
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'garment_type' => $item->garment_type,
                    'price' => $item->price,
                    'sizing_data' => $item->sizing_data,
                    'color_variants' => $item->color_variants,
                    'size_stock' => $item->size_stock,
                    'description' => substr($item->description ?? '', 0, 100) . '...',
                    'store' => $item->store ? $item->store->name : null,
                    'category' => $item->category,
                    'total_stock' => $item->stock_quantity,
                    'created_at' => $item->created_at,
                ];
            }),
            'expected_vs_actual' => [
                'ai_returned_name_1' => 'T Shirt 1',
                'db_actual_name_1' => $items->firstWhere('id', 1)->name ?? 'NOT FOUND',
                'ai_returned_price_1' => 19.99,
                'db_actual_price_1' => $items->firstWhere('id', 1)->price ?? 'NOT FOUND',
            ]
        ]);
    });

    // Test 3: Check Python pickle file vs database - FIXED PATH
    Route::get('/pickle-vs-database', function () {
        // CORRECTED PATH for your system
        $aiPath = 'C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai';
        $pickleFile = $aiPath . '/original_items.pkl';

        if (!file_exists($pickleFile)) {
            return response()->json([
                'error' => 'Pickle file not found at: ' . $pickleFile,
                'ai_path_exists' => file_exists($aiPath),
                'files_in_ai_folder' => file_exists($aiPath) ? array_slice(scandir($aiPath), 0, 20) : [],
            ]);
        }

        $pythonScript = <<<PYTHON
import sys
import pickle
import pandas as pd
import json
import os

pickle_path = r"{$pickleFile}"

try:
    df = pd.read_pickle(pickle_path)

    result = {
        'success': True,
        'pickle_info': {
            'total_rows': len(df),
            'total_columns': len(df.columns),
            'columns': list(df.columns),
            'first_3_rows': []
        }
    }

    for i in range(min(3, len(df))):
        row = df.iloc[i].to_dict()
        cleaned_row = {}
        for key, value in row.items():
            if isinstance(value, (pd.Timestamp)):
                cleaned_row[key] = str(value)
            elif pd.isna(value):
                cleaned_row[key] = None
            else:
                cleaned_row[key] = value
        result['pickle_info']['first_3_rows'].append(cleaned_row)

    result['pickle_info']['item_ids'] = df['ID'].tolist()[:20] if 'ID' in df.columns else []
    result['pickle_info']['names'] = df['Name'].tolist()[:10] if 'Name' in df.columns else []

except Exception as e:
    result = {
        'success': False,
        'error': str(e),
        'pickle_path': pickle_path
    }

print(json.dumps(result))
PYTHON;

        $tempFile = tempnam(sys_get_temp_dir(), 'pickle_') . '.py';
        file_put_contents($tempFile, $pythonScript);

        $output = shell_exec('python "' . $tempFile . '" 2>&1');
        unlink($tempFile);

        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false) {
            $jsonString = substr($output, $jsonStart);
            $pickleData = json_decode($jsonString, true);
        } else {
            $pickleData = ['raw_output' => $output];
        }

        $dbItems = \App\Models\Item::count();
        $dbSample = \App\Models\Item::take(3)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'garment_type' => $item->garment_type,
                'price' => $item->price,
            ];
        });

        return response()->json([
            'pickle_file_analysis' => $pickleData,
            'database_analysis' => [
                'total_items' => $dbItems,
                'sample_items' => $dbSample,
                'first_3_ids' => \App\Models\Item::orderBy('id')->take(3)->pluck('id')->toArray(),
                'first_3_names' => \App\Models\Item::orderBy('id')->take(3)->pluck('name')->toArray(),
            ],
            'comparison' => [
                'pickle_item_count' => $pickleData['pickle_info']['total_rows'] ?? 0,
                'database_item_count' => $dbItems,
                'match_percentage' => $dbItems > 0 ?
                    (($pickleData['pickle_info']['total_rows'] ?? 0) / $dbItems * 100) . '%' : 'N/A',
                'same_first_item' => isset($pickleData['pickle_info']['first_3_rows'][0]['Name']) &&
                    isset($dbSample[0]['name']) ?
                    $pickleData['pickle_info']['first_3_rows'][0]['Name'] === $dbSample[0]['name'] : false,
            ]
        ]);
    });

    // Test 4: Direct AI model check - FIXED PATH
    Route::get('/ai-model-data-check', function () {
        $aiPath = 'C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai';
        $artifactsDir = $aiPath . '/artifacts';

        $modelsToCheck = [
            "size_recommender_v2.pkl",
            "intelligent_outfit_builder.pkl",
            "complete_size_system.pkl",
            "complete_outfit_system.pkl"
        ];

        $pythonScript = <<<PYTHON
import sys
import pickle
import json
import os

ai_dir = r"{$aiPath}"
artifacts_dir = r"{$artifactsDir}"

models_to_check = [
    "size_recommender_v2.pkl",
    "intelligent_outfit_builder.pkl",
    "complete_size_system.pkl",
    "complete_outfit_system.pkl"
]

results = {}

for model_file in models_to_check:
    model_path = os.path.join(artifacts_dir, model_file)

    if not os.path.exists(model_path):
        results[model_file] = {"exists": False, "path": model_path}
        continue

    try:
        with open(model_path, 'rb') as f:
            model = pickle.load(f)

        model_info = {
            "exists": True,
            "type": str(type(model)),
            "size_bytes": os.path.getsize(model_path),
        }

        if hasattr(model, '__dict__'):
            attrs = {k: str(type(v))[:50] for k, v in model.__dict__.items() if not k.startswith('_')}
            model_info["attributes"] = attrs
            model_info["attribute_count"] = len(attrs)

        if hasattr(model, 'item_info'):
            if isinstance(model.item_info, dict):
                model_info["item_info_count"] = len(model.item_info)
                model_info["item_info_sample"] = list(model.item_info.keys())[:5] if model.item_info else []

        if hasattr(model, 'measurement_db'):
            if hasattr(model.measurement_db, '__len__'):
                model_info["measurement_db_count"] = len(model.measurement_db)

        if hasattr(model, 'items_df'):
            if hasattr(model.items_df, '__len__'):
                model_info["items_df_count"] = len(model.items_df)
                if hasattr(model.items_df, 'columns'):
                    model_info["items_df_columns"] = list(model.items_df.columns)[:10]

        results[model_file] = model_info

    except Exception as e:
        results[model_file] = {
            "exists": True,
            "load_error": str(e),
            "error_type": str(type(e))
        } 

print(json.dumps(results, indent=2))
PYTHON;

        $tempFile = tempnam(sys_get_temp_dir(), 'modelcheck_') . '.py';
        file_put_contents($tempFile, $pythonScript);

        $output = shell_exec('python "' . $tempFile . '" 2>&1');
        unlink($tempFile);

        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false) {
            $jsonString = substr($output, $jsonStart);
            $modelData = json_decode($jsonString, true);
        } else {
            $modelData = ['raw_output' => $output];
        }

        return response()->json([
            'ai_models_analysis' => $modelData,
            'paths_checked' => [
                'ai_path' => $aiPath,
                'artifacts_path' => $artifactsDir,
                'artifacts_exists' => file_exists($artifactsDir),
                'files_in_artifacts' => file_exists($artifactsDir) ? array_slice(scandir($artifactsDir), 0, 20) : [],
            ],
            'database_sample_for_comparison' => [
                'first_5_items' => \App\Models\Item::take(5)->get(['id', 'name', 'garment_type', 'price']),
                'item_id_range' => [
                    'min' => \App\Models\Item::min('id'),
                    'max' => \App\Models\Item::max('id'),
                ],
                'total_unique_garment_types' => \App\Models\Item::distinct()->count('garment_type'),
            ]
        ]);
    });
});

// ========== COMPREHENSIVE AI-DB MATCH TEST - FIXED PATH ==========
Route::get('/ai-db-match-test', function () {
    $dbItems = \App\Models\Item::inRandomOrder()->take(3)->get();

    $aiPath = 'C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai';
    $artifactsDir = $aiPath . '/artifacts';

    $pythonScript = <<<PYTHON
import sys
import json
import pickle
import os
import pandas as pd

ai_dir = r"{$aiPath}"
artifacts_dir = r"{$artifactsDir}"

test_measurements = {
    "chest_circumference": 95,
    "waist_circumference": 82,
    "garment_length": 75,
    "sleeve_length": 62
}

size_model_path = os.path.join(artifacts_dir, "size_recommender_v2.pkl")
outfit_model_path = os.path.join(artifacts_dir, "intelligent_outfit_builder.pkl")

results = {}

results["models_exist"] = {
    "size_recommender": os.path.exists(size_model_path),
    "outfit_builder": os.path.exists(outfit_model_path),
}

pickle_path = os.path.join(ai_dir, "original_items.pkl")
if os.path.exists(pickle_path):
    try:
        df = pd.read_pickle(pickle_path)
        results["pickle_data"] = {
            "total_rows": len(df),
            "columns": list(df.columns),
            "first_item_id": int(df.iloc[0]['ID']) if 'ID' in df.columns else None,
            "first_item_name": str(df.iloc[0]['Name']) if 'Name' in df.columns else None,
            "item_ids_in_pickle": df['ID'].tolist()[:10] if 'ID' in df.columns else []
        }
    except Exception as e:
        results["pickle_data"] = {"error": str(e)}
else:
    results["pickle_data"] = {"error": "Pickle file not found at: " + pickle_path}

features_path = os.path.join(artifacts_dir, "features_df.pkl")
if os.path.exists(features_path):
    try:
        features_df = pd.read_pickle(features_path)
        results["features_data"] = {
            "total_items": len(features_df),
            "columns": list(features_df.columns)[:10],
            "has_item_id": 'item_id' in features_df.columns,
            "item_ids_in_features": features_df['item_id'].tolist()[:10] if 'item_id' in features_df.columns else []
        }
    except Exception as e:
        results["features_data"] = {"error": str(e)}

print(json.dumps(results, indent=2))
PYTHON;

    $tempFile = tempnam(sys_get_temp_dir(), 'matchtest_') . '.py';
    file_put_contents($tempFile, $pythonScript);

    $output = shell_exec('python "' . $tempFile . '" 2>&1');
    unlink($tempFile);

    $jsonStart = strpos($output, '{');
    if ($jsonStart !== false) {
        $jsonString = substr($output, $jsonStart);
        $aiData = json_decode($jsonString, true);
    } else {
        $aiData = ['raw_output' => $output];
    }

    return response()->json([
        'database_random_items' => $dbItems->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'garment_type' => $item->garment_type,
                'price' => $item->price,
                'store' => $item->store ? $item->store->name : null,
                'category' => $item->category,
                'description_preview' => substr($item->description ?? '', 0, 50) . '...',
            ];
        }),
        'ai_system_data' => $aiData,
        'paths_info' => [
            'ai_path' => $aiPath,
            'ai_path_exists' => file_exists($aiPath),
            'artifacts_path' => $artifactsDir,
            'artifacts_exists' => file_exists($artifactsDir),
        ],
        'match_analysis' => [
            'database_has_items' => $dbItems->isNotEmpty(),
            'ai_has_pickle_data' => isset($aiData['pickle_data']['total_rows']),
            'potential_mismatch' => $dbItems->isNotEmpty() &&
                isset($aiData['pickle_data']['total_rows']) &&
                $aiData['pickle_data']['total_rows'] !== \App\Models\Item::count(),
            'suggestion' => $dbItems->isNotEmpty() &&
                (!isset($aiData['pickle_data']['total_rows']) ||
                 $aiData['pickle_data']['total_rows'] === 0)
                ? "AI is using MOCK DATA - original_items.pkl might be empty or wrong"
                : "Check if item IDs match between database and pickle file"
        ]
    ]);
});

Route::get('/inspect-pickle/{filename}', function ($filename) {
    $aiPath = 'C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai';
    $artifactsDir = $aiPath . '/artifacts';
    $filePath = $artifactsDir . '/' . $filename;

    if (!file_exists($filePath)) {
        return response()->json([
            'error' => 'File not found: ' . $filePath,
            'artifacts_dir_exists' => file_exists($artifactsDir),
            'files_in_dir' => file_exists($artifactsDir) ? array_slice(scandir($artifactsDir), 0, 20) : [],
        ], 404);
    }

    $inspectScript = <<<PYTHON
import sys
import os
import json
import pickle

ai_dir = r"{$aiPath}"
sys.path.insert(0, ai_dir)

file_path = r"{$filePath}"

try:
    with open(file_path, 'rb') as f:
        obj = pickle.load(f)

    result = {
        "success": True,
        "file_size_bytes": os.path.getsize(file_path),
        "file_size_mb": round(os.path.getsize(file_path) / 1024 / 1024, 2),
        "object_type": str(type(obj)),
    }

    if isinstance(obj, dict):
        result["object_kind"] = "dictionary"
        result["keys"] = list(obj.keys())
        result["key_count"] = len(obj)

        for key in list(obj.keys())[:5]:
            if isinstance(obj[key], (list, dict)):
                result[f"sample_{key}"] = str(type(obj[key]))
            else:
                result[f"sample_{key}"] = str(obj[key])[:100]
    else:
        result["object_kind"] = "class_instance"
        result["class_name"] = obj.__class__.__name__
        result["module"] = obj.__class__.__module__

        attrs = [attr for attr in dir(obj) if not attr.startswith('_')]
        result["attributes_count"] = len(attrs)
        result["attributes_sample"] = attrs[:10]

except Exception as e:
    result = {
        "success": False,
        "error": str(e),
        "file_path": file_path,
    }

print(json.dumps(result, indent=2))
PYTHON;

    $tempFile = tempnam(sys_get_temp_dir(), 'inspect_') . '.py';
    file_put_contents($tempFile, $inspectScript);

    $output = shell_exec('python "' . $tempFile . '" 2>&1');
    unlink($tempFile);

    $output = trim($output);
    $jsonStart = strpos($output, '{');

    if ($jsonStart !== false) {
        $jsonString = substr($output, $jsonStart);
        $data = json_decode($jsonString, true);
        return response()->json($data);
    }

    return response()->json(['raw_output' => $output]);
});

// ========== AI TEST ROUTES (Public for testing) ==========
Route::prefix('ai-test')->group(function () {
    // Test 1: Check AI folder - FIXED PATH
    Route::get('/check', function () {
        $aiPath = 'C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai';
        $artifactsPath = $aiPath . '/artifacts';

        $result = [
            'ai_path' => $aiPath,
            'ai_exists' => file_exists($aiPath),
            'artifacts_path' => $artifactsPath,
            'artifacts_exists' => file_exists($artifactsPath),
        ];

        if (file_exists($aiPath)) {
            $result['ai_folder_files'] = array_slice(scandir($aiPath), 0, 20);
        }
        
        if (file_exists($artifactsPath)) {
            $result['artifacts_files'] = array_slice(scandir($artifactsPath), 0, 20);
        }

        return response()->json($result);
    });

    // Test 2: Test Python directly
    Route::get('/test-python', function () {
        $aiPath = 'C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai';
        $script = $aiPath . '/test_paths.py';

        if (!file_exists($script)) {
            return response()->json(['error' => 'Test script not found at: ' . $script], 500);
        }

        $output = shell_exec('python "' . $script . '" 2>&1');

        return response()->json([
            'output' => $output,
            'script' => $script,
            'exists' => file_exists($script),
        ]);
    });

    // Test 3: Size recommendation test
    Route::post('/size-test', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'garmentType' => 'required|string',
            'chest' => 'required|numeric|min:70|max:150',
            'waist' => 'required|numeric|min:60|max:130',
        ]);

        $user = \App\Models\User::first();
        if (!$user) {
            $user = \App\Models\User::factory()->create();
        }

        $controller = new AIRecommendationController();
        $request->merge([
            'userMeasurements' => [
                'chest_circumference' => $validated['chest'],
                'waist_circumference' => $validated['waist'],
            ]
        ]);
        $request->setUserResolver(fn() => $user);

        return $controller->size($request, $user->id);
    });

    // Test 4: Outfit building test
    Route::post('/outfit-test', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'startingItemId' => 'required|integer',
            'chest' => 'nullable|numeric',
            'waist' => 'nullable|numeric',
        ]);

        $user = \App\Models\User::first();
        if (!$user) {
            $user = \App\Models\User::factory()->create();
        }

        $controller = new AIRecommendationController();
        $measurements = [];
        if ($request->has('chest') && $request->has('waist')) {
            $measurements = [
                'chest_circumference' => $validated['chest'],
                'waist_circumference' => $validated['waist'],
            ];
        }

        $request->merge([
            'startingItemId' => $validated['startingItemId'],
            'userMeasurements' => $measurements,
            'style' => 'casual_everyday',
            'maxItems' => 3
        ]);
        $request->setUserResolver(fn() => $user);

        return $controller->outfit($request, $user->id);
    });
});

// ========== PROTECTED AI ROUTES (Need auth) ==========
Route::prefix('ai')->middleware('auth:sanctum')->group(function () {
    Route::post('/users/{user}/size', [AIRecommendationController::class, 'size']);
    Route::post('/users/{user}/outfit', [AIRecommendationController::class, 'outfit']);
    Route::match(['get', 'post'], '/users/{user}/recommendations', [AIRecommendationController::class, 'recommendations']);
    Route::post('/users/{user}/sync', [AIRecommendationController::class, 'sync']);
});

// ========== PROTECTED USER ROUTES ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/password', [UserController::class, 'updatePassword']);
    Route::delete('/user', [UserController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');

    Route::get('/chat-support', [SupportChatController::class, 'index']);
    Route::post('/chat-support', [SupportChatController::class, 'store']);
    Route::get('/chat-support/{chatSupport}', [SupportChatController::class, 'show']);
    Route::post('/chat-support/{chatSupport}/reply', [SupportChatController::class, 'reply']);
});