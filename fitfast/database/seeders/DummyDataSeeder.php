<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Item;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // Get existing roles (already seeded in migration)
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        $storeAdminRole = DB::table('roles')->where('name', 'Store Admin')->first();
        $userRole = DB::table('roles')->where('name', 'User')->first();

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

        // Create 5 store admins
        for ($i = 1; $i <= 5; $i++) {
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

        $this->command->info('Created 105 users');

        // Create 10 test stores
        $stores = [];
        $storeAdmins = DB::table('users')->where('role_id', $storeAdminRole->id)->get();

        for ($i = 1; $i <= 10; $i++) {
            $stores[] = [
                'user_id' => $storeAdmins->random()->id,
                'name' => 'Fashion Store ' . $i,
                'description' => 'A great fashion store offering quality clothing and accessories. We provide the latest trends and best quality products.',
                'logo' => 'stores/logo' . $i . '.jpg',
                'banner_image' => 'stores/banner' . $i . '.jpg',
                'contact_info' => json_encode([
                    'phone' => '+123456789' . $i,
                    'email' => 'store' . $i . '@example.com',
                    'website' => 'https://store' . $i . '.com'
                ]),
                'address' => $i . ' Fashion Avenue, Shopping District, City, Country',
                'status' => 'active',
                'created_at' => now()->subDays(rand(30, 365)),
                'updated_at' => now(),
            ];
        }

        DB::table('stores')->insert($stores);

        $this->command->info('Created 10 stores');

        // Get existing categories
        $categories = DB::table('categories')->get();
        $this->command->info('Using existing categories: ' . $categories->count() . ' categories found');

        // Create curated test items
        $items = [];
        $stores = DB::table('stores')->get();

         $placeholderImage = 'items/placeholder.jpg';
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

             foreach ($categoryItems as $categorySlug => $itemDefinitions) {
            if (!isset($categoriesBySlug[$categorySlug])) {
                continue;
            }
              foreach ($itemDefinitions as $definition) {
                $sizeStock = $this->buildSizeStock();

                $items[] = [
                    'store_id' => $stores->random()->id,
                    'category_id' => $categoriesBySlug[$categorySlug]->id,
                    'name' => $definition['name'],
                    'description' => $this->buildDescription($definition['name']),
                    'price' => $definition['price'],
                    'sizing_data' => json_encode($this->buildSizingData($definition['garment_type'])),
                    'stock_quantity' => array_sum($sizeStock),
                    'color_variants' => json_encode($this->buildColorVariants($definition['colors'] ?? ['Black', 'Gray'], $placeholderImage)),
                    'size_stock' => json_encode($sizeStock),
                    'garment_type' => $definition['garment_type'],
                    'created_at' => now()->subDays(rand(1, 180)),
                    'updated_at' => now(),
                ];
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
        $this->command->info('- 105 Users (100 customers + 5 store admins)');
        $this->command->info('- 10 Stores');
        $this->command->info('- ' . count($items) . ' Items');
        $this->command->info('- 50 Orders');
        $this->command->info('- Wishlist items');
        $this->command->info('- Order items with proper sizing and color selection');
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
        ];}
         private function buildColorVariants(array $colors, string $imagePath): array
    {
        return array_map(function ($color) use ($imagePath) {
            return [
                'color' => $color,
                'hex_code' => $this->getColorHex($color),
                'images' => [$imagePath],
            ];
        }, $colors);
    }

    private function buildDescription(string $itemName): string
    {
        return $itemName . ' crafted with premium materials and a comfortable fit. Each piece ships with consistent sizing data and a universal product image for a clean catalog experience.';

        
    }
}
