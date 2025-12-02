<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        // Create 500 test items
        $items = [];
        $stores = DB::table('stores')->get();

        $garmentTypes = ['T-Shirt', 'Jeans', 'Dress', 'Shirt', 'Jacket', 'Sweater', 'Shorts', 'Skirt'];
        $colors = ['Red', 'Blue', 'Green', 'Black', 'White', 'Gray', 'Navy', 'Beige', 'Brown', 'Pink'];
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

        for ($i = 1; $i <= 500; $i++) {
            $sizeStock = [];
            foreach ($sizes as $size) {
                $sizeStock[$size] = rand(0, 50);
            }

            $colorVariants = [];
            $colorCount = rand(1, 3);
            $selectedColors = array_rand($colors, $colorCount);
            if (!is_array($selectedColors)) {
                $selectedColors = [$selectedColors];
            }
            foreach ($selectedColors as $colorIndex) {
                $colorVariants[] = [
                    'color' => $colors[$colorIndex],
                    'hex_code' => $this->getColorHex($colors[$colorIndex]),
                    'images' => ['items/color_' . strtolower($colors[$colorIndex]) . '_1.jpg']
                ];
            }

            $category = $categories->random();
            $garmentType = $this->getGarmentTypeForCategory($category->name, $garmentTypes);

            $items[] = [
                'store_id' => $stores->random()->id,
                'category_id' => $category->id,
                'name' => ucfirst($garmentType) . ' ' . $i . ' - ' . $colors[array_rand($colors)],
                'description' => 'High quality ' . strtolower($garmentType) . ' made from premium materials. Perfect for casual and formal occasions. Available in multiple sizes and colors.',
                'price' => rand(1999, 19999) / 100, // Random price between 19.99 and 199.99
                'sizing_data' => json_encode([
                    'fit' => ['Slim', 'Regular', 'Relaxed'][rand(0, 2)],
                    'material' => ['100% Cotton', 'Polyester Blend', 'Wool', 'Silk', 'Linen'][rand(0, 4)],
                    'care_instructions' => 'Machine wash cold, tumble dry low',
                    'country_of_origin' => ['China', 'Bangladesh', 'Vietnam', 'India'][rand(0, 3)]
                ]),
                'stock_quantity' => rand(10, 200),
                'color_variants' => json_encode($colorVariants),
                'size_stock' => json_encode($sizeStock),
                'garment_type' => $garmentType,
                'created_at' => now()->subDays(rand(1, 180)),
                'updated_at' => now(),
            ];
        }

        DB::table('items')->insert($items);

        $this->command->info('Created 500 items');

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
        $this->command->info('- 500 Items');
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

    private function getGarmentTypeForCategory($categoryName, $garmentTypes)
    {
        $categoryMapping = [
            'T-Shirts' => 'T-Shirt',
            'Jeans' => 'Jeans',
            'Dresses' => 'Dress',
            'Shirts' => 'Shirt',
            'Shoes' => 'Shoes',
            'Accessories' => 'Accessory'
        ];

        return $categoryMapping[$categoryName] ?? $garmentTypes[array_rand($garmentTypes)];
    }
}
