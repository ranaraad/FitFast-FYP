<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    private const STANDARD_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    private $standardSizeRanges = [
        'chest_circumference' => [
            'XS' => ['min' => 86, 'max' => 91],
            'S'  => ['min' => 91, 'max' => 97],
            'M'  => ['min' => 97, 'max' => 102],
            'L'  => ['min' => 102, 'max' => 107],
            'XL' => ['min' => 107, 'max' => 112],
            'XXL' => ['min' => 112, 'max' => 117],
        ],
        'waist_circumference' => [
            'XS' => ['min' => 71, 'max' => 76],
            'S'  => ['min' => 76, 'max' => 81],
            'M'  => ['min' => 81, 'max' => 86],
            'L'  => ['min' => 86, 'max' => 91],
            'XL' => ['min' => 91, 'max' => 96],
            'XXL' => ['min' => 96, 'max' => 101],
        ],
        'hips_circumference' => [
            'XS' => ['min' => 86, 'max' => 91],
            'S'  => ['min' => 91, 'max' => 96],
            'M'  => ['min' => 96, 'max' => 101],
            'L'  => ['min' => 101, 'max' => 106],
            'XL' => ['min' => 106, 'max' => 111],
            'XXL' => ['min' => 111, 'max' => 116],
        ],
        'garment_length' => [
            't_shirt' => ['XS' => 66, 'S' => 68, 'M' => 70, 'L' => 72, 'XL' => 74, 'XXL' => 76],
            'shirt' => ['XS' => 71, 'S' => 73, 'M' => 75, 'L' => 77, 'XL' => 79, 'XXL' => 81],
            'hoodie' => ['XS' => 68, 'S' => 70, 'M' => 72, 'L' => 74, 'XL' => 76, 'XXL' => 78],
            'jacket' => ['XS' => 70, 'S' => 72, 'M' => 74, 'L' => 76, 'XL' => 78, 'XXL' => 80],
            'dress' => ['XS' => 95, 'S' => 97, 'M' => 99, 'L' => 101, 'XL' => 103, 'XXL' => 105],
            'skirt' => ['XS' => 50, 'S' => 52, 'M' => 54, 'L' => 56, 'XL' => 58, 'XXL' => 60],
            'pants' => ['XS' => 100, 'S' => 102, 'M' => 104, 'L' => 106, 'XL' => 108, 'XXL' => 110],
            'shorts' => ['XS' => 45, 'S' => 47, 'M' => 49, 'L' => 51, 'XL' => 53, 'XXL' => 55],
            'underwear' => ['XS' => 35, 'S' => 36, 'M' => 37, 'L' => 38, 'XL' => 39, 'XXL' => 40],
        ],
        'sleeve_length' => [
            'XS' => 58, 'S' => 60, 'M' => 62, 'L' => 64, 'XL' => 66, 'XXL' => 68
        ],
        'shoulder_width' => [
            'XS' => 41, 'S' => 43, 'M' => 45, 'L' => 47, 'XL' => 49, 'XXL' => 51
        ],
        'inseam_length' => [
            'XS' => 76, 'S' => 78, 'M' => 80, 'L' => 82, 'XL' => 84, 'XXL' => 86
        ],
        'thigh_circumference' => [
            'XS' => ['min' => 51, 'max' => 53],
            'S'  => ['min' => 53, 'max' => 55],
            'M'  => ['min' => 55, 'max' => 57],
            'L'  => ['min' => 57, 'max' => 59],
            'XL' => ['min' => 59, 'max' => 61],
            'XXL' => ['min' => 61, 'max' => 63],
        ],
        'foot_length' => [
            'XS' => 24, 'S' => 25, 'M' => 26, 'L' => 27, 'XL' => 28, 'XXL' => 29
        ],
        'calf_circumference' => [
            'XS' => 33, 'S' => 35, 'M' => 37, 'L' => 39, 'XL' => 41, 'XXL' => 43
        ],
        'foot_width' => [
            'XS' => 9, 'S' => 9.5, 'M' => 10, 'L' => 10.5, 'XL' => 11, 'XXL' => 11.5
        ],
        'head_circumference' => [
            'XS' => 54, 'S' => 56, 'M' => 58, 'L' => 60, 'XL' => 62, 'XXL' => 64
        ],
        'brim_width' => [
            'XS' => 6, 'S' => 6.5, 'M' => 7, 'L' => 7.5, 'XL' => 8, 'XXL' => 8.5
        ],
        'height' => [
            'XS' => 40, 'S' => 45, 'M' => 50, 'L' => 55, 'XL' => 60, 'XXL' => 65
        ],
        'width' => [
            'XS' => 30, 'S' => 35, 'M' => 40, 'L' => 45, 'XL' => 50, 'XXL' => 55
        ],
        'depth' => [
            'XS' => 15, 'S' => 18, 'M' => 21, 'L' => 24, 'XL' => 27, 'XXL' => 30
        ],
        'length' => [
            'XS' => 40, 'S' => 45, 'M' => 50, 'L' => 55, 'XL' => 60, 'XXL' => 65
        ],
        'circumference' => [
            'XS' => 15, 'S' => 16, 'M' => 17, 'L' => 18, 'XL' => 19, 'XXL' => 20
        ],
    ];

    private $garmentMeasurementTemplates = [
        't_shirt' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'fitted_shirt' => ['chest_circumference', 'waist_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'dress_shirt' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'slim_pants' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening'],
        'regular_pants' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening'],
        'slim_jeans' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening'],
        'regular_jeans' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening'],
        'pullover_hoodie' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'hood_height'],
        'zip_hoodie' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'hood_height'],
        'bomber_jacket' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'denim_jacket' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'trench_coat' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'a_line_dress' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'garment_length'],
        'bodycon_dress' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'garment_length'],
        'yoga_pants' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference'],
        'casual_shorts' => ['waist_circumference', 'hips_circumference', 'inseam_length'],
        'training_shorts' => ['waist_circumference', 'hips_circumference', 'inseam_length'],
        'crewneck_sweater' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'a_line_skirt' => ['waist_circumference', 'hips_circumference', 'garment_length'],
        'swim_trunks' => ['waist_circumference', 'hips_circumference', 'inseam_length'],
        'bikini_top' => ['chest_circumference'],
        'briefs' => ['waist_circumference', 'hips_circumference'],
        'crew_socks' => ['foot_length', 'calf_circumference'],
        'sneakers' => ['foot_length', 'foot_width'],
        'dress_shoes' => ['foot_length', 'foot_width'],
        'backpack' => ['height', 'width', 'depth'],
        'tote_bag' => ['height', 'width', 'depth'],
        'necklace' => ['length', 'circumference'],
        'bracelet' => ['circumference'],
        'baseball_cap' => ['head_circumference', 'brim_width'],
        'beanie' => ['head_circumference'],
        // Additional garment types for more variety
        'v_neck_tee' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'henley_shirt' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'polo_shirt' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'cargo_pants' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening'],
        'cargo_shorts' => ['waist_circumference', 'hips_circumference', 'inseam_length'],
        'leggings' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference'],
        'windbreaker' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'puffer_jacket' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'cardigan' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'turtleneck' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
        'maxi_dress' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'garment_length'],
        'midi_dress' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'garment_length'],
        'wrap_dress' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'garment_length'],
        'pencil_skirt' => ['waist_circumference', 'hips_circumference', 'garment_length'],
        'tennis_skirt' => ['waist_circumference', 'hips_circumference', 'garment_length'],
        'board_shorts' => ['waist_circumference', 'hips_circumference', 'inseam_length'],
        'one_piece_swimsuit' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'garment_length'],
        'boxer_briefs' => ['waist_circumference', 'hips_circumference'],
        'ankle_socks' => ['foot_length', 'calf_circumference'],
        'loafers' => ['foot_length', 'foot_width'],
        'boots' => ['foot_length', 'foot_width'],
        'crossbody_bag' => ['height', 'width', 'depth'],
        'clutch' => ['height', 'width', 'depth'],
        'earrings' => ['length', 'circumference'],
        'ring' => ['circumference'],
        'sun_hat' => ['head_circumference', 'brim_width'],
        'bucket_hat' => ['head_circumference', 'brim_width'],
    ];

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

        // Create 250 test items
        $items = [];
        $stores = DB::table('stores')->get();
        
        // Expanded category items for 250 items total
        $categoryItems = [
            't-shirts' => [
                ['name' => 'Classic Crew Tee', 'garment_type' => 't_shirt', 'price' => 19.99, 'colors' => ['Black', 'White']],
                ['name' => 'V-Neck Tee', 'garment_type' => 'v_neck_tee', 'price' => 21.99, 'colors' => ['Gray', 'Navy']],
                ['name' => 'Graphic Print Tee', 'garment_type' => 't_shirt', 'price' => 24.99, 'colors' => ['Black', 'White', 'Red']],
                ['name' => 'Performance Training Tee', 'garment_type' => 't_shirt', 'price' => 24.99, 'colors' => ['Navy', 'Gray']],
                ['name' => 'Organic Cotton Tee', 'garment_type' => 't_shirt', 'price' => 29.99, 'colors' => ['White', 'Beige']],
            ],
            'shirts' => [
                ['name' => 'Oxford Button-Down', 'garment_type' => 'fitted_shirt', 'price' => 39.99, 'colors' => ['White', 'Blue']],
                ['name' => 'Executive Dress Shirt', 'garment_type' => 'dress_shirt', 'price' => 49.99, 'colors' => ['White', 'Blue']],
                ['name' => 'Polo Shirt', 'garment_type' => 'polo_shirt', 'price' => 34.99, 'colors' => ['Black', 'Navy', 'Green']],
                ['name' => 'Flannel Shirt', 'garment_type' => 'fitted_shirt', 'price' => 42.50, 'colors' => ['Red/Black', 'Blue/Black']],
                ['name' => 'Henley Shirt', 'garment_type' => 'henley_shirt', 'price' => 32.99, 'colors' => ['Gray', 'Navy']],
            ],
            'pants' => [
                ['name' => 'Slim Chino Pants', 'garment_type' => 'slim_pants', 'price' => 44.99, 'colors' => ['Khaki', 'Navy']],
                ['name' => 'Everyday Trousers', 'garment_type' => 'regular_pants', 'price' => 42.50, 'colors' => ['Black', 'Gray']],
                ['name' => 'Cargo Pants', 'garment_type' => 'cargo_pants', 'price' => 49.99, 'colors' => ['Olive', 'Black']],
                ['name' => 'Dress Pants', 'garment_type' => 'regular_pants', 'price' => 54.99, 'colors' => ['Charcoal', 'Navy']],
                ['name' => 'Linen Pants', 'garment_type' => 'regular_pants', 'price' => 47.50, 'colors' => ['Beige', 'White']],
            ],
            'jeans' => [
                ['name' => 'Classic Straight Jeans', 'garment_type' => 'regular_jeans', 'price' => 54.00, 'colors' => ['Blue', 'Black']],
                ['name' => 'Vintage Slim Jeans', 'garment_type' => 'slim_jeans', 'price' => 58.50, 'colors' => ['Blue', 'Gray']],
                ['name' => 'Relaxed Fit Jeans', 'garment_type' => 'regular_jeans', 'price' => 52.00, 'colors' => ['Dark Blue', 'Black']],
                ['name' => 'Stretch Skinny Jeans', 'garment_type' => 'slim_jeans', 'price' => 59.99, 'colors' => ['Black', 'Indigo']],
                ['name' => 'Bootcut Jeans', 'garment_type' => 'regular_jeans', 'price' => 56.50, 'colors' => ['Medium Blue', 'Dark Wash']],
            ],
            'shorts' => [
                ['name' => 'Weekend Chino Shorts', 'garment_type' => 'casual_shorts', 'price' => 32.00, 'colors' => ['Beige', 'Green']],
                ['name' => 'Cargo Shorts', 'garment_type' => 'cargo_shorts', 'price' => 35.99, 'colors' => ['Khaki', 'Olive']],
                ['name' => 'Athletic Shorts', 'garment_type' => 'training_shorts', 'price' => 29.99, 'colors' => ['Black', 'Gray', 'Navy']],
                ['name' => 'Denim Shorts', 'garment_type' => 'casual_shorts', 'price' => 38.50, 'colors' => ['Blue', 'Black']],
                ['name' => 'Linen Shorts', 'garment_type' => 'casual_shorts', 'price' => 41.00, 'colors' => ['White', 'Beige']],
            ],
            'dresses' => [
                ['name' => 'Flowy A-Line Dress', 'garment_type' => 'a_line_dress', 'price' => 79.00, 'colors' => ['Red', 'Black']],
                ['name' => 'Evening Bodycon Dress', 'garment_type' => 'bodycon_dress', 'price' => 92.50, 'colors' => ['Black', 'Navy']],
                ['name' => 'Maxi Dress', 'garment_type' => 'maxi_dress', 'price' => 85.00, 'colors' => ['Floral', 'Solid Black']],
                ['name' => 'Midi Dress', 'garment_type' => 'midi_dress', 'price' => 72.99, 'colors' => ['Blue', 'Green']],
                ['name' => 'Wrap Dress', 'garment_type' => 'wrap_dress', 'price' => 68.50, 'colors' => ['Polka Dot', 'Striped']],
            ],
            'skirts' => [
                ['name' => 'Pleated A-Line Skirt', 'garment_type' => 'a_line_skirt', 'price' => 36.75, 'colors' => ['Black', 'Pink']],
                ['name' => 'Pencil Skirt', 'garment_type' => 'pencil_skirt', 'price' => 42.99, 'colors' => ['Black', 'Navy']],
                ['name' => 'Tennis Skirt', 'garment_type' => 'tennis_skirt', 'price' => 34.50, 'colors' => ['White', 'Blue']],
                ['name' => 'Denim Skirt', 'garment_type' => 'a_line_skirt', 'price' => 39.99, 'colors' => ['Blue', 'Black']],
                ['name' => 'Midi Skirt', 'garment_type' => 'a_line_skirt', 'price' => 45.00, 'colors' => ['Floral', 'Solid']],
            ],
            'jackets' => [
                ['name' => 'Heritage Bomber Jacket', 'garment_type' => 'bomber_jacket', 'price' => 89.99, 'colors' => ['Green', 'Black']],
                ['name' => 'Classic Denim Jacket', 'garment_type' => 'denim_jacket', 'price' => 69.50, 'colors' => ['Blue', 'Black']],
                ['name' => 'Windbreaker', 'garment_type' => 'windbreaker', 'price' => 64.99, 'colors' => ['Blue', 'Red']],
                ['name' => 'Puffer Jacket', 'garment_type' => 'puffer_jacket', 'price' => 119.99, 'colors' => ['Black', 'Navy']],
                ['name' => 'Leather Jacket', 'garment_type' => 'bomber_jacket', 'price' => 149.99, 'colors' => ['Black', 'Brown']],
            ],
            'coats' => [
                ['name' => 'Storm Trench Coat', 'garment_type' => 'trench_coat', 'price' => 120.00, 'colors' => ['Beige', 'Black']],
                ['name' => 'Wool Overcoat', 'garment_type' => 'trench_coat', 'price' => 159.99, 'colors' => ['Gray', 'Navy']],
                ['name' => 'Rain Coat', 'garment_type' => 'trench_coat', 'price' => 89.99, 'colors' => ['Yellow', 'Blue']],
                ['name' => 'Peacoat', 'garment_type' => 'trench_coat', 'price' => 134.50, 'colors' => ['Navy', 'Black']],
            ],
            'sweaters' => [
                ['name' => 'Merino Crewneck Sweater', 'garment_type' => 'crewneck_sweater', 'price' => 74.00, 'colors' => ['Gray', 'Navy']],
                ['name' => 'Cashmere Cardigan', 'garment_type' => 'cardigan', 'price' => 129.99, 'colors' => ['Cream', 'Gray']],
                ['name' => 'Cable Knit Sweater', 'garment_type' => 'crewneck_sweater', 'price' => 84.50, 'colors' => ['Ivory', 'Navy']],
                ['name' => 'Turtleneck Sweater', 'garment_type' => 'turtleneck', 'price' => 67.99, 'colors' => ['Black', 'Burgundy']],
                ['name' => 'V-Neck Sweater', 'garment_type' => 'crewneck_sweater', 'price' => 59.99, 'colors' => ['Gray', 'Green']],
            ],
            'hoodies' => [
                ['name' => 'Everyday Pullover Hoodie', 'garment_type' => 'pullover_hoodie', 'price' => 55.00, 'colors' => ['Gray', 'Black']],
                ['name' => 'Zip-Up Hoodie', 'garment_type' => 'zip_hoodie', 'price' => 59.99, 'colors' => ['Navy', 'Black']],
                ['name' => 'Fleece Hoodie', 'garment_type' => 'pullover_hoodie', 'price' => 49.99, 'colors' => ['Charcoal', 'Royal Blue']],
                ['name' => 'Graphic Hoodie', 'garment_type' => 'pullover_hoodie', 'price' => 64.50, 'colors' => ['Black', 'White']],
            ],
            'activewear' => [
                ['name' => 'Studio Yoga Pants', 'garment_type' => 'yoga_pants', 'price' => 48.00, 'colors' => ['Black', 'Blue']],
                ['name' => 'Mesh Training Shorts', 'garment_type' => 'training_shorts', 'price' => 34.00, 'colors' => ['Black', 'Gray']],
                ['name' => 'Athletic Leggings', 'garment_type' => 'leggings', 'price' => 42.99, 'colors' => ['Black', 'Dark Gray']],
                ['name' => 'Running Tights', 'garment_type' => 'yoga_pants', 'price' => 52.50, 'colors' => ['Black', 'Blue']],
                ['name' => 'Training Tank Top', 'garment_type' => 't_shirt', 'price' => 28.99, 'colors' => ['Black', 'Gray']],
            ],
            'swimwear' => [
                ['name' => 'Triangle Bikini Top', 'garment_type' => 'bikini_top', 'price' => 29.99, 'colors' => ['Red', 'Blue']],
                ['name' => 'Quick-Dry Swim Trunks', 'garment_type' => 'swim_trunks', 'price' => 33.00, 'colors' => ['Blue', 'Green']],
                ['name' => 'One-Piece Swimsuit', 'garment_type' => 'one_piece_swimsuit', 'price' => 54.99, 'colors' => ['Black', 'Navy']],
                ['name' => 'Board Shorts', 'garment_type' => 'board_shorts', 'price' => 36.50, 'colors' => ['Floral', 'Solid']],
                ['name' => 'Rash Guard', 'garment_type' => 't_shirt', 'price' => 39.99, 'colors' => ['Blue', 'Black']],
            ],
            'underwear' => [
                ['name' => 'Soft Cotton Briefs', 'garment_type' => 'briefs', 'price' => 14.00, 'colors' => ['White', 'Black']],
                ['name' => 'Boxer Briefs', 'garment_type' => 'boxer_briefs', 'price' => 16.99, 'colors' => ['Gray', 'Navy']],
                ['name' => 'Silk Boxers', 'garment_type' => 'briefs', 'price' => 24.50, 'colors' => ['Black', 'Burgundy']],
                ['name' => 'Seamless Panties', 'garment_type' => 'briefs', 'price' => 12.99, 'colors' => ['Nude', 'Black']],
            ],
            'socks' => [
                ['name' => 'Cushioned Crew Socks', 'garment_type' => 'crew_socks', 'price' => 9.50, 'colors' => ['Black', 'White']],
                ['name' => 'No-Show Socks', 'garment_type' => 'ankle_socks', 'price' => 7.99, 'colors' => ['Black', 'White', 'Gray']],
                ['name' => 'Compression Socks', 'garment_type' => 'crew_socks', 'price' => 14.99, 'colors' => ['Black', 'Navy']],
                ['name' => 'Wool Hiking Socks', 'garment_type' => 'crew_socks', 'price' => 18.50, 'colors' => ['Gray', 'Green']],
            ],
            'shoes' => [
                ['name' => 'Everyday Sneakers', 'garment_type' => 'sneakers', 'price' => 79.99, 'colors' => ['White', 'Black']],
                ['name' => 'Polished Dress Shoes', 'garment_type' => 'dress_shoes', 'price' => 110.00, 'colors' => ['Brown', 'Black']],
                ['name' => 'Running Shoes', 'garment_type' => 'sneakers', 'price' => 99.99, 'colors' => ['Blue', 'Gray']],
                ['name' => 'Leather Loafers', 'garment_type' => 'loafers', 'price' => 124.50, 'colors' => ['Brown', 'Black']],
                ['name' => 'Ankle Boots', 'garment_type' => 'boots', 'price' => 139.99, 'colors' => ['Black', 'Brown']],
            ],
            'bags' => [
                ['name' => 'Commute Backpack', 'garment_type' => 'backpack', 'price' => 64.00, 'colors' => ['Black', 'Gray']],
                ['name' => 'Canvas Tote Bag', 'garment_type' => 'tote_bag', 'price' => 38.00, 'colors' => ['Beige', 'Blue']],
                ['name' => 'Crossbody Bag', 'garment_type' => 'crossbody_bag', 'price' => 72.99, 'colors' => ['Black', 'Brown']],
                ['name' => 'Leather Clutch', 'garment_type' => 'clutch', 'price' => 89.50, 'colors' => ['Black', 'Red']],
                ['name' => 'Weekend Duffel', 'garment_type' => 'backpack', 'price' => 94.99, 'colors' => ['Navy', 'Gray']],
            ],
            'jewelry' => [
                ['name' => 'Minimalist Necklace', 'garment_type' => 'necklace', 'price' => 27.50, 'colors' => ['Silver', 'Gold']],
                ['name' => 'Braided Bracelet', 'garment_type' => 'bracelet', 'price' => 21.00, 'colors' => ['Brown', 'Black']],
                ['name' => 'Stud Earrings', 'garment_type' => 'earrings', 'price' => 18.99, 'colors' => ['Silver', 'Gold']],
                ['name' => 'Signet Ring', 'garment_type' => 'ring', 'price' => 34.50, 'colors' => ['Silver', 'Gold']],
                ['name' => 'Statement Necklace', 'garment_type' => 'necklace', 'price' => 42.99, 'colors' => ['Gold', 'Rose Gold']],
            ],
            'hats' => [
                ['name' => 'Classic Baseball Cap', 'garment_type' => 'baseball_cap', 'price' => 22.00, 'colors' => ['Black', 'Blue']],
                ['name' => 'Ribbed Beanie', 'garment_type' => 'beanie', 'price' => 18.00, 'colors' => ['Gray', 'Black']],
                ['name' => 'Sun Hat', 'garment_type' => 'sun_hat', 'price' => 29.99, 'colors' => ['White', 'Beige']],
                ['name' => 'Bucket Hat', 'garment_type' => 'bucket_hat', 'price' => 24.50, 'colors' => ['Black', 'Green']],
                ['name' => 'Wool Fedora', 'garment_type' => 'baseball_cap', 'price' => 39.99, 'colors' => ['Gray', 'Brown']],
            ],
        ];

        $categoriesBySlug = $categories->keyBy('slug');

        // Counter to ensure we create exactly 250 items
        $targetItemCount = 250;
        $createdItems = 0;
        $allItems = [];

        // First, create items from category definitions
        foreach ($categoryItems as $categorySlug => $itemDefinitions) {
            if (!isset($categoriesBySlug[$categorySlug])) {
                continue;
            }

            foreach ($itemDefinitions as $definition) {
                if ($createdItems >= $targetItemCount) {
                    break 2; // Break out of both loops
                }

                $sizeStock = $this->buildRealisticSizeStock();
                $colorVariants = $this->buildColorVariants($definition['colors']);
                $variants = $this->buildVariants($colorVariants, $sizeStock);
                $sizingData = $this->buildRealisticSizingData($definition['garment_type']);

                $allItems[] = [
                    'store_id' => $stores->random()->id,
                    'category_id' => $categoriesBySlug[$categorySlug]->id,
                    'name' => $definition['name'],
                    'description' => $this->buildDescription($definition['name']),
                    'price' => $definition['price'],
                    'sizing_data' => json_encode($sizingData),
                    'stock_quantity' => array_sum($sizeStock),
                    'color_variants' => json_encode($colorVariants),
                    'variants' => json_encode($variants),
                    'size_stock' => json_encode($sizeStock),
                    'garment_type' => $definition['garment_type'],
                    'created_at' => now()->subDays(rand(1, 180)),
                    'updated_at' => now(),
                ];
                
                $createdItems++;
            }
        }

        // If we need more items, create additional random items
        if ($createdItems < $targetItemCount) {
            $additionalNeeded = $targetItemCount - $createdItems;
            $this->command->info("Creating {$additionalNeeded} additional random items...");
            
            // List of all possible garment types
            $allGarmentTypes = array_keys($this->garmentMeasurementTemplates);
            
            // List of color combinations
            $colorCombinations = [
                ['Black', 'White'],
                ['Blue', 'Gray'],
                ['Navy', 'White'],
                ['Green', 'Black'],
                ['Red', 'Black'],
                ['Brown', 'Beige'],
                ['Gray', 'Black'],
                ['White', 'Blue'],
                ['Black', 'Red'],
                ['Navy', 'Gray'],
            ];
            
            // Item name templates by category
            $nameTemplates = [
                't-shirt' => ['Basic Tee', 'Cotton Tee', 'Essential Tee', 'Premium Tee'],
                'shirt' => ['Classic Shirt', 'Modern Shirt', 'Essential Shirt', 'Premium Shirt'],
                'pants' => ['Essential Pants', 'Comfort Pants', 'Classic Trousers', 'Modern Pants'],
                'jeans' => ['Classic Jeans', 'Modern Jeans', 'Essential Jeans', 'Premium Denim'],
                'dress' => ['Essential Dress', 'Classic Dress', 'Modern Dress', 'Chic Dress'],
                'jacket' => ['Essential Jacket', 'Modern Jacket', 'Classic Coat', 'Premium Outerwear'],
            ];

            for ($i = 0; $i < $additionalNeeded; $i++) {
                // Pick a random category
                $category = $categories->random();
                
                // Pick a random garment type
                $garmentType = $allGarmentTypes[array_rand($allGarmentTypes)];
                
                // Pick random colors
                $colors = $colorCombinations[array_rand($colorCombinations)];
                
                // Generate name based on garment type
                $namePrefix = '';
                if (strpos($garmentType, 't_shirt') !== false) $namePrefix = 't-shirt';
                elseif (strpos($garmentType, 'shirt') !== false) $namePrefix = 'shirt';
                elseif (strpos($garmentType, 'pants') !== false || strpos($garmentType, 'jeans') !== false) $namePrefix = 'pants';
                elseif (strpos($garmentType, 'dress') !== false) $namePrefix = 'dress';
                elseif (strpos($garmentType, 'jacket') !== false || strpos($garmentType, 'coat') !== false) $namePrefix = 'jacket';
                else $namePrefix = 'item';
                
                $nameTemplate = $nameTemplates[$namePrefix] ?? ['Essential Item', 'Classic Item', 'Modern Item'];
                $name = $nameTemplate[array_rand($nameTemplate)] . ' ' . ($i + 1);
                
                // Generate realistic price based on garment type
                $price = $this->getRealisticPrice($garmentType);
                
                $sizeStock = $this->buildRealisticSizeStock();
                $colorVariants = $this->buildColorVariants($colors);
                $variants = $this->buildVariants($colorVariants, $sizeStock);
                $sizingData = $this->buildRealisticSizingData($garmentType);

                $allItems[] = [
                    'store_id' => $stores->random()->id,
                    'category_id' => $category->id,
                    'name' => $name,
                    'description' => $this->buildDescription($name),
                    'price' => $price,
                    'sizing_data' => json_encode($sizingData),
                    'stock_quantity' => array_sum($sizeStock),
                    'color_variants' => json_encode($colorVariants),
                    'variants' => json_encode($variants),
                    'size_stock' => json_encode($sizeStock),
                    'garment_type' => $garmentType,
                    'created_at' => now()->subDays(rand(1, 180)),
                    'updated_at' => now(),
                ];
                
                $createdItems++;
            }
        }

        // Insert all items in batches to avoid memory issues
        $chunks = array_chunk($allItems, 50);
        foreach ($chunks as $chunk) {
            DB::table('items')->insert($chunk);
        }

        $this->command->info("Created {$createdItems} curated items with realistic ML-ready sizing data");

        // Get all items for further processing
        $items = DB::table('items')->get();

        // Create item_user relationships (wishlist/favorites) - scale up proportionally
        $itemUser = [];
        $customers = DB::table('users')->where('role_id', $userRole->id)->get();
        
        // Scale wishlist items proportionally to item count (approx 8x original)
        $wishlistCount = min(200 * 8, $items->count() * 2);
        
        for ($i = 0; $i < $wishlistCount; $i++) {
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

        // Scale up orders proportionally (approx 4x original)
        $orderCount = 50 * 4;
        $orders = [];
        $customers = DB::table('users')->where('role_id', $userRole->id)->get();
        $stores = DB::table('stores')->get();

        $orderStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

        for ($i = 1; $i <= $orderCount; $i++) {
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

        $this->command->info("Created {$orderCount} orders");

        // Create order items and update order totals
        $orderItems = [];
        $orders = DB::table('orders')->get();

        foreach ($orders as $order) {
            $orderTotal = 0;
            $itemCount = rand(1, 5);
            $selectedItems = $items->random($itemCount);

            foreach ($selectedItems as $item) {
                $quantity = rand(1, 3);
                $unitPrice = $item->price;
                $subtotal = $quantity * $unitPrice;
                $orderTotal += $subtotal;

                // Get available colors from color_variants
                $colorVariants = json_decode($item->color_variants, true);
                $availableColors = array_keys($colorVariants);
                $selectedColor = $availableColors[array_rand($availableColors)];

                // Get available sizes from size_stock
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
        $this->command->info("- {$createdItems} Items with realistic sizing data");
        $this->command->info("- {$orderCount} Orders");
        $this->command->info('- ' . count($uniqueItemUser) . ' Wishlist items');
        $this->command->info('- Order items with proper sizing and color selection');
    }

    private function getRealisticPrice(string $garmentType): float
    {
        $priceRanges = [
            't_shirt' => [15.99, 29.99],
            'v_neck_tee' => [17.99, 32.99],
            'henley_shirt' => [22.99, 39.99],
            'polo_shirt' => [24.99, 44.99],
            'fitted_shirt' => [34.99, 59.99],
            'dress_shirt' => [39.99, 69.99],
            'slim_pants' => [39.99, 69.99],
            'regular_pants' => [37.99, 64.99],
            'cargo_pants' => [44.99, 74.99],
            'slim_jeans' => [49.99, 79.99],
            'regular_jeans' => [44.99, 74.99],
            'casual_shorts' => [29.99, 54.99],
            'cargo_shorts' => [32.99, 59.99],
            'training_shorts' => [24.99, 44.99],
            'a_line_dress' => [59.99, 99.99],
            'bodycon_dress' => [69.99, 119.99],
            'maxi_dress' => [64.99, 109.99],
            'midi_dress' => [54.99, 94.99],
            'wrap_dress' => [49.99, 89.99],
            'a_line_skirt' => [29.99, 59.99],
            'pencil_skirt' => [34.99, 64.99],
            'tennis_skirt' => [27.99, 54.99],
            'bomber_jacket' => [69.99, 129.99],
            'denim_jacket' => [59.99, 109.99],
            'windbreaker' => [49.99, 94.99],
            'puffer_jacket' => [89.99, 169.99],
            'trench_coat' => [89.99, 159.99],
            'pullover_hoodie' => [44.99, 84.99],
            'zip_hoodie' => [49.99, 89.99],
            'crewneck_sweater' => [59.99, 109.99],
            'cardigan' => [64.99, 119.99],
            'turtleneck' => [54.99, 99.99],
            'yoga_pants' => [39.99, 74.99],
            'leggings' => [34.99, 64.99],
            'bikini_top' => [24.99, 44.99],
            'swim_trunks' => [29.99, 54.99],
            'one_piece_swimsuit' => [44.99, 84.99],
            'board_shorts' => [29.99, 59.99],
            'briefs' => [9.99, 24.99],
            'boxer_briefs' => [12.99, 29.99],
            'crew_socks' => [6.99, 19.99],
            'ankle_socks' => [5.99, 16.99],
            'sneakers' => [59.99, 129.99],
            'dress_shoes' => [79.99, 159.99],
            'loafers' => [89.99, 169.99],
            'boots' => [99.99, 199.99],
            'backpack' => [49.99, 99.99],
            'tote_bag' => [29.99, 69.99],
            'crossbody_bag' => [54.99, 119.99],
            'clutch' => [39.99, 89.99],
            'necklace' => [19.99, 59.99],
            'bracelet' => [14.99, 44.99],
            'earrings' => [12.99, 39.99],
            'ring' => [24.99, 69.99],
            'baseball_cap' => [17.99, 39.99],
            'beanie' => [14.99, 34.99],
            'sun_hat' => [22.99, 49.99],
            'bucket_hat' => [19.99, 44.99],
        ];

        $range = $priceRanges[$garmentType] ?? [19.99, 99.99];
        return round(rand($range[0] * 100, $range[1] * 100) / 100, 2);
    }

    private function buildRealisticSizingData(string $garmentType): array
    {
        $sizes = self::STANDARD_SIZES;
        $measurements = [];

        // Get the measurement template for this garment type
        $template = $this->garmentMeasurementTemplates[$garmentType] ?? [];

        foreach ($sizes as $size) {
            $sizeData = [];

            foreach ($template as $measurement) {
                $value = $this->getRealisticMeasurement($garmentType, $measurement, $size);
                if ($value !== null) {
                    $sizeData[$measurement] = (string) $value;
                }
            }

            // Always include data for all sizes
            $measurements[$size] = $sizeData;
        }

        // Ensure size progression is logical
        $measurements = $this->ensureSizeProgression($measurements);

        return [
            'garment_type' => $garmentType,
            'measurements_cm' => $measurements,
            'fit_characteristics' => [
                'fit_type' => $this->getRealisticFit($garmentType),
                'ease' => $this->getRealisticEase($garmentType),
                'stretch' => $this->getRealisticStretch($garmentType),
            ],
            'size_system' => 'US',
            'last_updated' => now()->toISOString(),
        ];
    }

    private function ensureSizeProgression(array $measurements): array
    {
        $sizes = self::STANDARD_SIZES;

        foreach ($measurements as $size => $data) {
            if ($size !== 'XS') {
                $prevSize = $sizes[array_search($size, $sizes) - 1];
                foreach ($data as $measure => $value) {
                    if (is_numeric($value)) {
                        $prevValue = $measurements[$prevSize][$measure] ?? 0;
                        if ($value < $prevValue) {
                            // Ensure current size is >= previous size plus a small increment
                            $increment = $this->getMeasurementIncrement($measure);
                            $measurements[$size][$measure] = (string) ($prevValue + $increment);
                        }
                    }
                }
            }
        }

        return $measurements;
    }

    private function getMeasurementIncrement(string $measurement): int
    {
        // Define reasonable increments between sizes for different measurements
        $increments = [
            'chest_circumference' => rand(3, 5),
            'waist_circumference' => rand(3, 5),
            'hips_circumference' => rand(3, 5),
            'garment_length' => rand(2, 4),
            'sleeve_length' => rand(2, 3),
            'shoulder_width' => rand(1, 2),
            'inseam_length' => rand(2, 3),
            'thigh_circumference' => rand(1, 2),
            'leg_opening' => rand(1, 2),
            'foot_length' => rand(1, 1),
            'foot_width' => rand(0.5, 1),
            'calf_circumference' => rand(1, 2),
            'head_circumference' => rand(1, 2),
            'brim_width' => rand(0.5, 1),
            'height' => rand(3, 5),
            'width' => rand(3, 5),
            'depth' => rand(2, 3),
            'length' => rand(3, 5),
            'circumference' => rand(1, 2),
            'hood_height' => 0, // Usually consistent
        ];

        return $increments[$measurement] ?? rand(1, 3);
    }

    private function getRealisticMeasurement(string $garmentType, string $measurement, string $size): ?int
    {
        // Return realistic measurements based on industry standards

        switch ($measurement) {
            case 'chest_circumference':
                $range = $this->standardSizeRanges['chest_circumference'][$size];
                return rand($range['min'], $range['max']);

            case 'waist_circumference':
                $range = $this->standardSizeRanges['waist_circumference'][$size];
                return rand($range['min'], $range['max']);

            case 'hips_circumference':
                $range = $this->standardSizeRanges['hips_circumference'][$size];
                return rand($range['min'], $range['max']);

            case 'garment_length':
                $garmentTypeForLength = $this->getGarmentCategoryForLength($garmentType);
                return $this->standardSizeRanges['garment_length'][$garmentTypeForLength][$size] ?? 70;

            case 'sleeve_length':
                return $this->standardSizeRanges['sleeve_length'][$size];

            case 'shoulder_width':
                return $this->standardSizeRanges['shoulder_width'][$size];

            case 'inseam_length':
                return $this->standardSizeRanges['inseam_length'][$size];

            case 'thigh_circumference':
                $range = $this->standardSizeRanges['thigh_circumference'][$size];
                return rand($range['min'], $range['max']);

            case 'leg_opening':
                // Leg opening: XS: 32-34cm, S: 34-36cm, M: 36-38cm, L: 38-40cm, XL: 40-42cm, XXL: 42-44cm
                $sizeIndex = array_search($size, self::STANDARD_SIZES);
                $base = 32 + ($sizeIndex * 2);
                return rand($base, $base + 2);

            case 'hood_height':
                // Hood height: consistent ~30cm for all sizes
                return 30;

            case 'foot_length':
                return $this->standardSizeRanges['foot_length'][$size];

            case 'foot_width':
                return $this->standardSizeRanges['foot_width'][$size] * 10; // Convert to mm for consistency

            case 'calf_circumference':
                return $this->standardSizeRanges['calf_circumference'][$size];

            case 'head_circumference':
                return $this->standardSizeRanges['head_circumference'][$size];

            case 'brim_width':
                return $this->standardSizeRanges['brim_width'][$size] * 10; // Convert to mm

            case 'height':
                return $this->standardSizeRanges['height'][$size];

            case 'width':
                return $this->standardSizeRanges['width'][$size];

            case 'depth':
                return $this->standardSizeRanges['depth'][$size];

            case 'length':
                return $this->standardSizeRanges['length'][$size];

            case 'circumference':
                return $this->standardSizeRanges['circumference'][$size];

            default:
                return null;
        }
    }

    private function getGarmentCategoryForLength(string $garmentType): string
    {
        if (strpos($garmentType, 'shirt') !== false) return 'shirt';
        if (strpos($garmentType, 'hoodie') !== false) return 'hoodie';
        if (strpos($garmentType, 'jacket') !== false || strpos($garmentType, 'coat') !== false) return 'jacket';
        if (strpos($garmentType, 'dress') !== false) return 'dress';
        if (strpos($garmentType, 'skirt') !== false) return 'skirt';
        if (strpos($garmentType, 'pants') !== false || strpos($garmentType, 'jeans') !== false) return 'pants';
        if (strpos($garmentType, 'shorts') !== false) return 'shorts';
        if (strpos($garmentType, 'underwear') !== false) return 'underwear';
        return 't_shirt';
    }

    private function getRealisticFit(string $garmentType): string
    {
        $fitMap = [
            't_shirt' => 'regular',
            'v_neck_tee' => 'regular',
            'henley_shirt' => 'regular',
            'polo_shirt' => 'regular',
            'fitted_shirt' => 'fitted',
            'dress_shirt' => 'slim',
            'slim_pants' => 'slim',
            'regular_pants' => 'regular',
            'cargo_pants' => 'regular',
            'slim_jeans' => 'slim',
            'regular_jeans' => 'regular',
            'casual_shorts' => 'regular',
            'cargo_shorts' => 'regular',
            'training_shorts' => 'regular',
            'pullover_hoodie' => 'relaxed',
            'zip_hoodie' => 'regular',
            'bomber_jacket' => 'regular',
            'denim_jacket' => 'regular',
            'windbreaker' => 'regular',
            'puffer_jacket' => 'regular',
            'trench_coat' => 'fitted',
            'a_line_dress' => 'a_line',
            'bodycon_dress' => 'bodycon',
            'maxi_dress' => 'relaxed',
            'midi_dress' => 'regular',
            'wrap_dress' => 'regular',
            'yoga_pants' => 'fitted',
            'leggings' => 'fitted',
            'crewneck_sweater' => 'regular',
            'cardigan' => 'regular',
            'turtleneck' => 'regular',
            'a_line_skirt' => 'a_line',
            'pencil_skirt' => 'slim',
            'tennis_skirt' => 'regular',
            'swim_trunks' => 'regular',
            'board_shorts' => 'regular',
            'bikini_top' => 'fitted',
            'one_piece_swimsuit' => 'fitted',
            'briefs' => 'regular',
            'boxer_briefs' => 'regular',
            'crew_socks' => 'regular',
            'ankle_socks' => 'regular',
            'sneakers' => 'regular',
            'dress_shoes' => 'regular',
            'loafers' => 'regular',
            'boots' => 'regular',
            'backpack' => 'regular',
            'tote_bag' => 'regular',
            'crossbody_bag' => 'regular',
            'clutch' => 'regular',
            'necklace' => 'regular',
            'bracelet' => 'regular',
            'earrings' => 'regular',
            'ring' => 'regular',
            'baseball_cap' => 'regular',
            'beanie' => 'regular',
            'sun_hat' => 'regular',
            'bucket_hat' => 'regular',
        ];

        return $fitMap[$garmentType] ?? 'regular';
    }

    private function getRealisticEase(string $garmentType): string
    {
        // Ease: how much extra room beyond body measurements
        $easeMap = [
            't_shirt' => 'standard',
            'v_neck_tee' => 'standard',
            'henley_shirt' => 'standard',
            'polo_shirt' => 'standard',
            'fitted_shirt' => 'tight',
            'dress_shirt' => 'standard',
            'slim_pants' => 'tight',
            'regular_pants' => 'standard',
            'cargo_pants' => 'standard',
            'slim_jeans' => 'tight',
            'regular_jeans' => 'standard',
            'casual_shorts' => 'standard',
            'cargo_shorts' => 'standard',
            'training_shorts' => 'standard',
            'pullover_hoodie' => 'relaxed',
            'zip_hoodie' => 'standard',
            'bomber_jacket' => 'standard',
            'denim_jacket' => 'standard',
            'windbreaker' => 'standard',
            'puffer_jacket' => 'relaxed',
            'trench_coat' => 'standard',
            'yoga_pants' => 'tight',
            'leggings' => 'tight',
            'crewneck_sweater' => 'standard',
            'cardigan' => 'standard',
            'turtleneck' => 'standard',
            'a_line_dress' => 'relaxed',
            'bodycon_dress' => 'tight',
            'maxi_dress' => 'relaxed',
            'midi_dress' => 'standard',
            'wrap_dress' => 'standard',
            'a_line_skirt' => 'standard',
            'pencil_skirt' => 'tight',
            'tennis_skirt' => 'standard',
            'swim_trunks' => 'standard',
            'board_shorts' => 'standard',
            'bikini_top' => 'tight',
            'one_piece_swimsuit' => 'tight',
            'briefs' => 'standard',
            'boxer_briefs' => 'standard',
            'crew_socks' => 'standard',
            'ankle_socks' => 'standard',
            'sneakers' => 'standard',
            'dress_shoes' => 'standard',
            'loafers' => 'standard',
            'boots' => 'standard',
            'backpack' => 'standard',
            'tote_bag' => 'standard',
            'crossbody_bag' => 'standard',
            'clutch' => 'standard',
            'necklace' => 'standard',
            'bracelet' => 'standard',
            'earrings' => 'standard',
            'ring' => 'standard',
            'baseball_cap' => 'standard',
            'beanie' => 'standard',
            'sun_hat' => 'standard',
            'bucket_hat' => 'standard',
        ];

        return $easeMap[$garmentType] ?? 'standard';
    }

    private function getRealisticStretch(string $garmentType): string
    {
        $stretchMap = [
            't_shirt' => 'medium',
            'v_neck_tee' => 'medium',
            'henley_shirt' => 'medium',
            'polo_shirt' => 'medium',
            'fitted_shirt' => 'low',
            'dress_shirt' => 'low',
            'slim_pants' => 'medium',
            'regular_pants' => 'low',
            'cargo_pants' => 'low',
            'slim_jeans' => 'low',
            'regular_jeans' => 'low',
            'casual_shorts' => 'medium',
            'cargo_shorts' => 'low',
            'training_shorts' => 'high',
            'pullover_hoodie' => 'medium',
            'zip_hoodie' => 'medium',
            'bomber_jacket' => 'medium',
            'denim_jacket' => 'low',
            'windbreaker' => 'low',
            'puffer_jacket' => 'low',
            'trench_coat' => 'low',
            'yoga_pants' => 'high',
            'leggings' => 'high',
            'crewneck_sweater' => 'low',
            'cardigan' => 'low',
            'turtleneck' => 'low',
            'a_line_dress' => 'low',
            'bodycon_dress' => 'high',
            'maxi_dress' => 'low',
            'midi_dress' => 'low',
            'wrap_dress' => 'medium',
            'a_line_skirt' => 'low',
            'pencil_skirt' => 'low',
            'tennis_skirt' => 'medium',
            'swim_trunks' => 'medium',
            'board_shorts' => 'low',
            'bikini_top' => 'high',
            'one_piece_swimsuit' => 'high',
            'briefs' => 'medium',
            'boxer_briefs' => 'medium',
            'crew_socks' => 'medium',
            'ankle_socks' => 'medium',
            'sneakers' => 'medium',
            'dress_shoes' => 'low',
            'loafers' => 'low',
            'boots' => 'low',
            'backpack' => 'low',
            'tote_bag' => 'low',
            'crossbody_bag' => 'low',
            'clutch' => 'low',
            'necklace' => 'low',
            'bracelet' => 'low',
            'earrings' => 'low',
            'ring' => 'low',
            'baseball_cap' => 'low',
            'beanie' => 'high',
            'sun_hat' => 'low',
            'bucket_hat' => 'low',
        ];

        return $stretchMap[$garmentType] ?? 'medium';
    }

    private function buildRealisticSizeStock(): array
    {
        $sizeStock = [];
        $sizes = self::STANDARD_SIZES;

        // Realistic stock distribution: M and L have most stock
        $stockDistribution = [
            'XS' => rand(5, 15),
            'S' => rand(10, 25),
            'M' => rand(20, 40),
            'L' => rand(15, 35),
            'XL' => rand(8, 20),
            'XXL' => rand(5, 15),
        ];

        foreach ($stockDistribution as $size => $stock) {
            if (in_array($size, $sizes)) {
                $sizeStock[$size] = $stock;
            }
        }

        return $sizeStock;
    }

    private function buildColorVariants(array $colors): array
    {
        $variants = [];
        foreach ($colors as $color) {
            $variants[$color] = [
                'name' => $color,
                'stock' => rand(10, 100)
            ];
        }
        return $variants;
    }

    private function buildVariants(array $colorVariants, array $sizeStock): array
    {
        $variants = [];
        $colors = array_keys($colorVariants);
        $sizes = array_keys($sizeStock);

        // Make a copy of sizeStock to track allocations
        $remainingSizeStock = $sizeStock;

        foreach ($colors as $color) {
            $colorStock = $colorVariants[$color]['stock'];
            $allocatedStock = 0;
            $attempts = 0;

            // Try to allocate this color's stock across sizes
            while ($allocatedStock < $colorStock && $attempts < 100) {
                $attempts++;

                // Pick a random size that still has stock
                $availableSizes = array_keys(array_filter($remainingSizeStock, function($stock) {
                    return $stock > 0;
                }));

                if (empty($availableSizes)) {
                    break;
                }

                $size = $availableSizes[array_rand($availableSizes)];
                $remainingForColor = $colorStock - $allocatedStock;
                $maxAllocation = min($remainingSizeStock[$size], $remainingForColor);

                if ($maxAllocation > 0) {
                    $allocation = rand(1, $maxAllocation);
                    $variants[] = [
                        'color' => $color,
                        'size' => $size,
                        'stock' => $allocation
                    ];
                    $allocatedStock += $allocation;
                    $remainingSizeStock[$size] -= $allocation;
                }
            }

            // If we couldn't allocate all stock, create more variants
            if ($allocatedStock < $colorStock && $attempts >= 100) {
                $remainingStock = $colorStock - $allocatedStock;
                // Distribute remaining stock across all sizes
                $perSize = ceil($remainingStock / count($sizes));
                foreach ($sizes as $size) {
                    if ($remainingStock <= 0) break;
                    $allocate = min($perSize, $remainingStock);
                    $variants[] = [
                        'color' => $color,
                        'size' => $size,
                        'stock' => $allocate
                    ];
                    $remainingStock -= $allocate;
                }
            }
        }

        return $variants;
    }

    private function buildDescription(string $itemName): string
    {
        $adjectives = ['Premium', 'High-quality', 'Comfortable', 'Stylish', 'Durable', 'Versatile'];
        $materials = ['cotton', 'polyester blend', 'wool', 'linen', 'silk', 'technical fabric'];
        $features = ['perfect fit', 'excellent comfort', 'modern design', 'timeless style', 'easy care'];

        $adjective = $adjectives[array_rand($adjectives)];
        $material = $materials[array_rand($materials)];
        $feature = $features[array_rand($features)];

        return $itemName . ". Made from {$adjective} {$material} for {$feature}. Includes detailed sizing data for accurate fit prediction.";
    }
}