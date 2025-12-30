<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
         $categories = [
            ['name' => 'T-Shirts', 'slug' => 't-shirts', 'type' => 'clothing', 'sort_order' => 1, 'description' => 'Everyday tees and athletic tops.'],
            ['name' => 'Shirts', 'slug' => 'shirts', 'type' => 'clothing', 'sort_order' => 2, 'description' => 'Button-downs and fitted shirts.'],
            ['name' => 'Pants', 'slug' => 'pants', 'type' => 'clothing', 'sort_order' => 3, 'description' => 'Chinos, trousers, and casual pants.'],
            ['name' => 'Jeans', 'slug' => 'jeans', 'type' => 'clothing', 'sort_order' => 4, 'description' => 'Denim fits for every style.'],
            ['name' => 'Shorts', 'slug' => 'shorts', 'type' => 'clothing', 'sort_order' => 5, 'description' => 'Casual and training shorts.'],
            ['name' => 'Dresses', 'slug' => 'dresses', 'type' => 'clothing', 'sort_order' => 6, 'description' => 'Day dresses and occasion wear.'],
            ['name' => 'Skirts', 'slug' => 'skirts', 'type' => 'clothing', 'sort_order' => 7, 'description' => 'A-line, pencil, and more.'],
            ['name' => 'Jackets', 'slug' => 'jackets', 'type' => 'clothing', 'sort_order' => 8, 'description' => 'Lightweight layers and denim jackets.'],
            ['name' => 'Coats', 'slug' => 'coats', 'type' => 'clothing', 'sort_order' => 9, 'description' => 'Outerwear built for cold weather.'],
            ['name' => 'Sweaters', 'slug' => 'sweaters', 'type' => 'clothing', 'sort_order' => 10, 'description' => 'Knitwear essentials.'],
            ['name' => 'Hoodies', 'slug' => 'hoodies', 'type' => 'clothing', 'sort_order' => 11, 'description' => 'Pullovers and zip-ups for layering.'],
            ['name' => 'Activewear', 'slug' => 'activewear', 'type' => 'clothing', 'sort_order' => 12, 'description' => 'Performance gear for training.'],
            ['name' => 'Swimwear', 'slug' => 'swimwear', 'type' => 'clothing', 'sort_order' => 13, 'description' => 'Swim trunks, bikinis, and more.'],
            ['name' => 'Underwear', 'slug' => 'underwear', 'type' => 'clothing', 'sort_order' => 14, 'description' => 'Base layers and intimates.'],
            ['name' => 'Socks', 'slug' => 'socks', 'type' => 'clothing', 'sort_order' => 15, 'description' => 'Everyday and performance socks.'],
            ['name' => 'Shoes', 'slug' => 'shoes', 'type' => 'footwear', 'sort_order' => 16, 'description' => 'Sneakers and dress shoes.'],
            ['name' => 'Bags', 'slug' => 'bags', 'type' => 'accessories', 'sort_order' => 17, 'description' => 'Backpacks, totes, and carryalls.'],
            ['name' => 'Jewelry', 'slug' => 'jewelry', 'type' => 'accessories', 'sort_order' => 18, 'description' => 'Necklaces, bracelets, and accents.'],
            ['name' => 'Hats', 'slug' => 'hats', 'type' => 'accessories', 'sort_order' => 19, 'description' => 'Caps, beanies, and sun hats.'],
        ];

         foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
