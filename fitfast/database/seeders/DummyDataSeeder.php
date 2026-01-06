<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Item;

class DummyDataSeeder extends Seeder
{
    private const CATEGORY_IMAGE_LIBRARY = [
        't-shirts' => [
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=900&q=80',
        ],
        'shirts' => [
            'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
        ],
        'pants' => [
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1469395446868-fb6a048d5ca3?auto=format&fit=crop&w=900&q=80',
        ],
        'jeans' => [
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
        ],
        'shorts' => [
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
        ],
        'dresses' => [
            'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1524504388940-0c3a3074e0b2?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
        ],
        'skirts' => [
            'https://images.unsplash.com/photo-1490111718993-d98654ce6cf7?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80',
        ],
        'jackets' => [
            'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1523380666135-d8effd30d60e?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
        ],
        'coats' => [
            'https://images.unsplash.com/photo-1495385794356-15371f348c31?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80',
        ],
        'sweaters' => [
            'https://images.unsplash.com/photo-1475180098004-ca77a66827be?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
        ],
        'hoodies' => [
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1523380666135-d8effd30d60e?auto=format&fit=crop&w=900&q=80',
        ],
        'activewear' => [
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1526402466671-4aa1ebf42c34?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
        ],
        'swimwear' => [
            'https://images.unsplash.com/photo-1524504388940-0c3a3074e0b2?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
        ],
        'underwear' => [
            'https://images.unsplash.com/photo-1524504388940-0c3a3074e0b2?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
        ],
        'socks' => [
            'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
        ],
        'shoes' => [
            'https://images.unsplash.com/photo-1528701800489-20be3c6c9460?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1523381294911-8d3cead13475?auto=format&fit=crop&w=900&q=80',
        ],
        'bags' => [
            'https://images.unsplash.com/photo-1524504388940-0c3a3074e0b2?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80',
        ],
        'jewelry' => [
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1505159940484-eb2b9f2588e2?auto=format&fit=crop&w=900&q=80',
        ],
        'hats' => [
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80',
        ],
        'default' => [
            'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
        ],
    ];

    private const STORE_LOGO_COLORS = [
        '641b2e',
        'be5b50',
        '942341',
        '7d2339',
        '3a1b24',
        'be8c8a',
    ];

    private const STORE_BANNER_NUMBERS = [1, 2, 3, 4];
    public function run()
    {
        $this->resetSeededData();

        // Get existing roles (already seeded in migration)
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        $storeAdminRole = DB::table('roles')->where('name', 'Store Admin')->first();
        $userRole = DB::table('roles')->where('name', 'User')->first();

        $storeAdminTotal = 20;

        // Create 100 test users (customers)
        $users = [];
        for ($i = 1; $i <= 100; $i++) {
            $users[] = [
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role_id' => $userRole->id,
                'measurements' => json_encode([
                    'height' => rand(160, 190) . 'cm',
                    'chest' => rand(85, 110) . 'cm',
                    'waist' => rand(70, 95) . 'cm',
                    'hips' => rand(90, 120) . 'cm',
                    'shoe_size' => rand(6, 12),
                ]),
                'address' => $i . ' Customer Street, City, Country',
                'shipping_address' => $i . ' Shipping Street, City, Country',
                'billing_address' => $i . ' Billing Street, City, Country',
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ];
        }

        // Create 20 store admins for curated stores
        for ($i = 1; $i <= $storeAdminTotal; $i++) {
            $users[] = [
                'name' => 'Store Admin ' . $i,
                'email' => 'storeadmin' . $i . '@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role_id' => $storeAdminRole->id,
                'measurements' => null,
                'address' => $i . ' Admin Street, City, Country',
                'shipping_address' => null,
                'billing_address' => null,
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($users);

        $this->command->info('Created ' . count($users) . ' users');

        $storeCatalog = $this->curatedStores();
        $storeAdmins = DB::table('users')
            ->where('role_id', $storeAdminRole->id)
            ->orderBy('id')
            ->get()
            ->values();

        $curatedStores = [];

        foreach ($storeCatalog as $index => $storeDefinition) {
            [$logoUrl, $bannerUrl] = $this->resolveStoreBranding($index + 1, $storeDefinition['name']);
            $storeAdmin = $storeAdmins->get($index) ?? $storeAdmins->random();
            $slug = Str::slug($storeDefinition['name']);

            $storeId = DB::table('stores')->insertGetId([
                'user_id' => $storeAdmin->id,
                'name' => $storeDefinition['name'],
                'description' => $storeDefinition['tagline'],
                'logo' => $logoUrl,
                'banner_image' => $bannerUrl,
                'contact_info' => json_encode([
                    'phone' => '+123456' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'email' => $slug . '@example.com',
                    'website' => 'https://' . $slug . '.example.com',
                ]),
                'address' => ($index + 1) . ' Fashion Avenue, Trend District, City, Country',
                'status' => 'active',
                'created_at' => now()->subDays(rand(30, 365)),
                'updated_at' => now(),
            ]);

            $curatedStores[] = [
                'id' => $storeId,
                'definition' => $storeDefinition,
            ];
        }

        $this->command->info('Created ' . count($curatedStores) . ' curated stores');

        // Get existing categories
        $categories = DB::table('categories')->get();
        $this->command->info('Using existing categories: ' . $categories->count() . ' categories found');

        // Create curated test items per store
        $items = [];

        $categoryItems = [
            't-shirts' => [
                ['name' => 'Classic Crew Tee', 'garment_type' => 't_shirt', 'price' => 19.99, 'colors' => ['Black', 'White']],
                ['name' => 'Performance Training Tee', 'garment_type' => 't_shirt', 'price' => 24.99, 'colors' => ['Navy', 'Gray']],
            ],
            'shirts' => [
                ['name' => 'Oxford Button-Down', 'garment_type' => 'fitted_shirt', 'price' => 39.99],
                ['name' => 'Executive Dress Shirt', 'garment_type' => 'dress_shirt', 'price' => 49.99, 'colors' => ['White', 'Blue']],
            ],
            'pants' => [
                ['name' => 'Slim Chino Pants', 'garment_type' => 'slim_pants', 'price' => 44.99],
                ['name' => 'Everyday Trousers', 'garment_type' => 'regular_pants', 'price' => 42.50],
            ],
            'jeans' => [
                ['name' => 'Classic Straight Jeans', 'garment_type' => 'regular_jeans', 'price' => 54.00],
                ['name' => 'Vintage Slim Jeans', 'garment_type' => 'slim_jeans', 'price' => 58.50],
            ],
            'shorts' => [
                ['name' => 'Weekend Chino Shorts', 'garment_type' => 'casual_shorts', 'price' => 32.00],
            ],
            'dresses' => [
                ['name' => 'Flowy A-Line Dress', 'garment_type' => 'a_line_dress', 'price' => 79.00],
                ['name' => 'Evening Bodycon Dress', 'garment_type' => 'bodycon_dress', 'price' => 92.50],
            ],
            'skirts' => [
                ['name' => 'Pleated A-Line Skirt', 'garment_type' => 'a_line_skirt', 'price' => 36.75],
            ],
            'jackets' => [
                ['name' => 'Heritage Bomber Jacket', 'garment_type' => 'bomber_jacket', 'price' => 89.99],
                ['name' => 'Classic Denim Jacket', 'garment_type' => 'denim_jacket', 'price' => 69.50],
            ],
            'coats' => [
                ['name' => 'Storm Trench Coat', 'garment_type' => 'trench_coat', 'price' => 120.00],
            ],
            'sweaters' => [
                ['name' => 'Merino Crewneck Sweater', 'garment_type' => 'crewneck_sweater', 'price' => 74.00],
            ],
            'hoodies' => [
                ['name' => 'Everyday Pullover Hoodie', 'garment_type' => 'pullover_hoodie', 'price' => 55.00],
            ],
            'activewear' => [
                ['name' => 'Studio Yoga Pants', 'garment_type' => 'yoga_pants', 'price' => 48.00],
                ['name' => 'Mesh Training Shorts', 'garment_type' => 'training_shorts', 'price' => 34.00],
            ],
            'swimwear' => [
                ['name' => 'Triangle Bikini Top', 'garment_type' => 'bikini_top', 'price' => 29.99],
                ['name' => 'Quick-Dry Swim Trunks', 'garment_type' => 'swim_trunks', 'price' => 33.00],
            ],
            'underwear' => [
                ['name' => 'Soft Cotton Briefs', 'garment_type' => 'briefs', 'price' => 14.00],
            ],
            'socks' => [
                ['name' => 'Cushioned Crew Socks', 'garment_type' => 'crew_socks', 'price' => 9.50],
            ],
            'shoes' => [
                ['name' => 'Everyday Sneakers', 'garment_type' => 'sneakers', 'price' => 79.99],
                ['name' => 'Polished Dress Shoes', 'garment_type' => 'dress_shoes', 'price' => 110.00],
            ],
            'bags' => [
                ['name' => 'Commute Backpack', 'garment_type' => 'backpack', 'price' => 64.00],
                ['name' => 'Canvas Tote Bag', 'garment_type' => 'tote_bag', 'price' => 38.00],
            ],
            'jewelry' => [
                ['name' => 'Minimalist Necklace', 'garment_type' => 'necklace', 'price' => 27.50],
                ['name' => 'Braided Bracelet', 'garment_type' => 'bracelet', 'price' => 21.00],
            ],
            'hats' => [
                ['name' => 'Classic Baseball Cap', 'garment_type' => 'baseball_cap', 'price' => 22.00],
                ['name' => 'Ribbed Beanie', 'garment_type' => 'beanie', 'price' => 18.00],
            ],
        ];

        $categoriesBySlug = $categories->keyBy('slug');

        foreach ($curatedStores as $storeEntry) {
            $storeDefinition = $storeEntry['definition'];
            $storeId = $storeEntry['id'];
            $label = $storeDefinition['style_prefix'] ?? $storeDefinition['name'];
            $focusCategories = $storeDefinition['focus_categories'] ?? [];

            foreach ($focusCategories as $categorySlug) {
                if (!isset($categoriesBySlug[$categorySlug])) {
                    continue;
                }

                $itemDefinitions = $categoryItems[$categorySlug] ?? [];

                foreach ($itemDefinitions as $definition) {
                    $sizeStock = $this->buildSizeStock();
                    $imageSet = $this->resolveItemImages($categorySlug);
                    $price = max(5, round($definition['price'] * (1 + rand(-5, 10) / 100), 2));

                    $items[] = [
                        'store_id' => $storeId,
                        'category_id' => $categoriesBySlug[$categorySlug]->id,
                        'name' => $definition['name'] . ' - ' . $label,
                        'description' => $this->buildDescription($definition['name']),
                        'price' => $price,
                        'sizing_data' => json_encode($this->buildSizingData($definition['garment_type'])),
                        'stock_quantity' => array_sum($sizeStock),
                        'color_variants' => json_encode($this->buildColorVariants($definition['colors'] ?? ['Black', 'Gray'], $imageSet)),
                        'size_stock' => json_encode($sizeStock),
                        'garment_type' => $definition['garment_type'],
                        'created_at' => now()->subDays(rand(1, 120)),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('items')->insert($items);

        $this->command->info('Created ' . count($items) . ' curated items');

        // Create item_user relationships (wishlist/favorites)
        $itemUser = [];
        $customers = DB::table('users')->where('role_id', $userRole->id)->get();
        $items = DB::table('items')->get();

        for ($i = 0; $i < 200; $i++) {
            $itemUser[] = [
                'item_id' => $items->random()->id,
                'user_id' => $customers->random()->id,
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => now(),
            ];
        }

        // Remove duplicates
        $uniqueItemUser = [];
        foreach ($itemUser as $record) {
            $key = $record['item_id'] . '-' . $record['user_id'];
            if (!isset($uniqueItemUser[$key])) {
                $uniqueItemUser[$key] = $record;
            }
        }

        DB::table('item_user')->insert(array_values($uniqueItemUser));

        $this->command->info('Created ' . count($uniqueItemUser) . ' wishlist items');

        // Create 50 test orders
        $orders = [];
        $customers = DB::table('users')->where('role_id', $userRole->id)->get();
        $stores = DB::table('stores')->get();

        $orderStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

        for ($i = 1; $i <= 50; $i++) {
            $customer = $customers->random();
            $store = $stores->random();
            $orderDate = now()->subDays(rand(1, 60));

            $orders[] = [
                'store_id' => $store->id,
                'user_id' => $customer->id,
                'total_amount' => 0, // Will update after order items
                'status' => $orderStatuses[array_rand($orderStatuses)],
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ];
        }

        DB::table('orders')->insert($orders);

        $this->command->info('Created 50 orders');

        // Create order items and update order totals
        $orderItems = [];
        $orders = DB::table('orders')->get();
        $items = DB::table('items')->get();

        foreach ($orders as $order) {
            $orderTotal = 0;
            $itemCount = rand(1, 5);
            $selectedItems = $items->random($itemCount);

            foreach ($selectedItems as $item) {
                $quantity = rand(1, 3);
                $unitPrice = $item->price;
                $subtotal = $quantity * $unitPrice;
                $orderTotal += $subtotal;

                $itemData = json_decode($item->color_variants, true);
                $availableColors = array_column($itemData, 'color');
                $selectedColor = $availableColors[array_rand($availableColors)];

                $sizeStock = json_decode($item->size_stock, true);
                $availableSizes = array_keys(array_filter($sizeStock, function($stock) {
                    return $stock > 0;
                }));
                $selectedSize = !empty($availableSizes) ? $availableSizes[array_rand($availableSizes)] : 'M';

                $orderItems[] = [
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'selected_size' => $selectedSize,
                    'selected_color' => $selectedColor,
                    'selected_brand' => 'Brand ' . rand(1, 20),
                    'unit_price' => $unitPrice,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ];
            }

            // Update order total
            DB::table('orders')->where('id', $order->id)->update(['total_amount' => $orderTotal]);
        }

        DB::table('order_items')->insert($orderItems);

        $this->command->info('Created order items');

        $this->command->info('Dummy data created successfully!');
        $this->command->info('- ' . count($users) . ' Users (100 customers + ' . $storeAdminTotal . ' store admins)');
        $this->command->info('- ' . count($curatedStores) . ' Stores');
        $this->command->info('- ' . count($items) . ' Items');
        $this->command->info('- 50 Orders');
        $this->command->info('- Wishlist items');
        $this->command->info('- Order items with proper sizing and color selection');
    }

    private function curatedStores(): array
    {
        return [
            [
                'name' => 'Urban Threads Collective',
                'tagline' => 'Streetwear staples built for city living with eco-conscious fabrics.',
                'focus_categories' => ['t-shirts', 'hoodies', 'jeans'],
                'style_prefix' => 'Urban Threads',
            ],
            [
                'name' => 'Coastal Linen House',
                'tagline' => 'Breezy resort silhouettes and flowing linens inspired by coastal getaways.',
                'focus_categories' => ['dresses', 'skirts', 'bags'],
                'style_prefix' => 'Coastal Linen',
            ],
            [
                'name' => 'Alpine Outerwear Co.',
                'tagline' => 'Layered insulation and weatherproof shells for alpine adventures.',
                'focus_categories' => ['jackets', 'coats', 'sweaters'],
                'style_prefix' => 'Alpine Outerwear',
            ],
            [
                'name' => 'MetroFit Active Lab',
                'tagline' => 'Performance gear engineered for studio sessions and urban workouts.',
                'focus_categories' => ['activewear', 'shorts', 't-shirts'],
                'style_prefix' => 'MetroFit',
            ],
            [
                'name' => 'Luxe Formals Studio',
                'tagline' => 'Tailored suiting and refined separates for elevated occasions.',
                'focus_categories' => ['shirts', 'pants', 'shoes'],
                'style_prefix' => 'Luxe Formals',
            ],
            [
                'name' => 'Soleil Swim Club',
                'tagline' => 'Bold swim sets and sunny accessories for the poolside.',
                'focus_categories' => ['swimwear', 'hats', 'bags'],
                'style_prefix' => 'Soleil Swim',
            ],
            [
                'name' => 'Velvet Evening Atelier',
                'tagline' => 'Evening-ready silhouettes with velvet textures and luminous accents.',
                'focus_categories' => ['dresses', 'jewelry', 'coats'],
                'style_prefix' => 'Velvet Atelier',
            ],
            [
                'name' => 'Heritage Denim Supply',
                'tagline' => 'Selvage denim and timeless workwear built to last.',
                'focus_categories' => ['jeans', 'shirts', 'jackets'],
                'style_prefix' => 'Heritage Denim',
            ],
            [
                'name' => 'Canvas Casual Co.',
                'tagline' => 'Weekender essentials and relaxed basics for everyday comfort.',
                'focus_categories' => ['t-shirts', 'pants', 'socks'],
                'style_prefix' => 'Canvas Casual',
            ],
            [
                'name' => 'Summit Trail Outfitters',
                'tagline' => 'Trail-tested outerwear and traction footwear for summit seekers.',
                'focus_categories' => ['jackets', 'activewear', 'shoes'],
                'style_prefix' => 'Summit Trail',
            ],
            [
                'name' => 'Bloom Boutique',
                'tagline' => 'Floral-inspired collections and feminine details for every occasion.',
                'focus_categories' => ['dresses', 'skirts', 'jewelry'],
                'style_prefix' => 'Bloom Boutique',
            ],
            [
                'name' => 'Pulse Streetwear Hub',
                'tagline' => 'Graphic-driven drops blending sport heritage with street culture.',
                'focus_categories' => ['hoodies', 't-shirts', 'hats'],
                'style_prefix' => 'Pulse Streetwear',
            ],
            [
                'name' => 'Crafted Comfort Knits',
                'tagline' => 'Soft knits and cozy layers spun for restorative days indoors.',
                'focus_categories' => ['sweaters', 'hoodies', 'pants'],
                'style_prefix' => 'Comfort Knits',
            ],
            [
                'name' => 'Midnight Accessories Bar',
                'tagline' => 'Statement accessories that elevate late-night looks instantly.',
                'focus_categories' => ['bags', 'jewelry', 'hats'],
                'style_prefix' => 'Midnight Bar',
            ],
            [
                'name' => 'Aero Performance Lab',
                'tagline' => 'Aerodynamic training essentials tuned for intense movement.',
                'focus_categories' => ['activewear', 'shirts', 'socks'],
                'style_prefix' => 'Aero Lab',
            ],
            [
                'name' => 'Serene Basics Lounge',
                'tagline' => 'Everyday intimates and plush layers for unhurried mornings.',
                'focus_categories' => ['underwear', 't-shirts', 'sweaters'],
                'style_prefix' => 'Serene Basics',
            ],
            [
                'name' => 'Terra Boot Co.',
                'tagline' => 'Rugged boots and earthy staples built for terrain shifts.',
                'focus_categories' => ['shoes', 'pants', 'jackets'],
                'style_prefix' => 'Terra Boot',
            ],
            [
                'name' => 'Radiant Resort Wear',
                'tagline' => 'Resort-ready dresses and luxe swim looks for endless vacations.',
                'focus_categories' => ['swimwear', 'dresses', 'hats'],
                'style_prefix' => 'Radiant Resort',
            ],
            [
                'name' => 'Ember Leatherworks',
                'tagline' => 'Handcrafted leather staples with burnished finishes.',
                'focus_categories' => ['jackets', 'bags', 'jewelry'],
                'style_prefix' => 'Ember Leather',
            ],
            [
                'name' => 'Wanderlite Travel Gear',
                'tagline' => 'Travel-smart layers and carryalls made for lighter journeys.',
                'focus_categories' => ['bags', 'shoes', 'coats'],
                'style_prefix' => 'Wanderlite',
            ],
        ];
    }

    private function getColorHex($colorName)
    {
        $colorMap = [
            'Red' => '#FF0000',
            'Blue' => '#0000FF',
            'Green' => '#008000',
            'Black' => '#000000',
            'White' => '#FFFFFF',
            'Gray' => '#808080',
            'Navy' => '#000080',
            'Beige' => '#F5F5DC',
            'Brown' => '#A52A2A',
            'Pink' => '#FFC0CB',
        ];

        return $colorMap[$colorName] ?? '#CCCCCC';
    }

    private function buildSizeStock(): array
    {
        $sizeStock = [];

        foreach (Item::STANDARD_SIZES as $size) {
            $sizeStock[$size] = rand(5, 25);
        }

        return $sizeStock;
    }

    private function buildSizingData(string $garmentType): array
    {
        $measurements = [];

        foreach (Item::getRequiredMeasurements($garmentType) as $measurement) {
            $measurements[$measurement] = rand(30, 120);
        }

        return [
            'fit' => ['Slim', 'Regular', 'Relaxed'][rand(0, 2)],
            'material' => ['100% Cotton', 'Polyester Blend', 'Wool', 'Silk', 'Linen'][rand(0, 4)],
            'care_instructions' => 'Machine wash cold, tumble dry low',
            'country_of_origin' => ['China', 'Bangladesh', 'Vietnam', 'India'][rand(0, 3)],
            'measurements_cm' => $measurements,
        ];
    }

    private function buildColorVariants(array $colors, array $imageSet): array
    {
        $images = $imageSet ?: self::CATEGORY_IMAGE_LIBRARY['default'];
        $variants = [];
        $imageCount = count($images);

        foreach (array_values($colors) as $index => $color) {
            $rotated = $images;
            if ($imageCount > 1) {
                $rotation = $index % $imageCount;
                $rotated = array_slice(array_merge($images, $images), $rotation, $imageCount);
            }

            $variants[] = [
                'color' => $color,
                'hex_code' => $this->getColorHex($color),
                'images' => $rotated,
            ];
        }

        return $variants;
    }

    private function resolveItemImages(string $categorySlug): array
    {
        $library = self::CATEGORY_IMAGE_LIBRARY[$categorySlug] ?? self::CATEGORY_IMAGE_LIBRARY['default'];
        $pool = $library ?: self::CATEGORY_IMAGE_LIBRARY['default'];
        $pool = array_values(array_unique($pool));
        $count = count($pool);

        if ($count === 0) {
            return self::CATEGORY_IMAGE_LIBRARY['default'];
        }

        $take = min(3, $count);
        if ($count <= $take) {
            return $pool;
        }

        $start = rand(0, $count - $take);
        return array_slice($pool, $start, $take);
    }

    private function resolveStoreBranding(int $index, string $storeName): array
    {
        $initial = strtoupper(Str::substr(trim($storeName), 0, 1) ?: 'S');
        $colors = self::STORE_LOGO_COLORS;
        $background = $colors[($index - 1) % count($colors)];

        $logoUrl = sprintf(
            'https://dummyimage.com/240x240/%s/ffffff.png&text=%s',
            $background,
            rawurlencode($initial)
        );

        $bannerNumbers = self::STORE_BANNER_NUMBERS;
        $bannerNumber = $bannerNumbers[($index - 1) % count($bannerNumbers)] ?? 1;
        $bannerUrl = sprintf(
            'https://dummyimage.com/1400x420/ebe0de/3a1b24.png&text=%s',
            rawurlencode((string) $bannerNumber)
        );

        return [$logoUrl, $bannerUrl];
    }

    private function buildDescription(string $itemName): string
    {
        return $itemName . ' crafted with premium materials and a comfortable fit. Each piece ships with consistent sizing data and a curated gallery so the catalog feels real.';
    }

    private function resetSeededData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            DB::table('order_items')->truncate();
            DB::table('orders')->truncate();
            DB::table('item_user')->truncate();
            DB::table('items')->truncate();
            DB::table('stores')->truncate();

            DB::table('users')
                ->where(fn ($query) => $query
                    ->where('email', 'like', 'customer%@example.com')
                    ->orWhere('email', 'like', 'storeadmin%@example.com'))
                ->delete();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
