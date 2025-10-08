<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $clothingCategories = [
            ['name' => 'T-Shirts', 'slug' => 't-shirts', 'type' => 'clothing', 'sort_order' => 1],
            ['name' => 'Shirts', 'slug' => 'shirts', 'type' => 'clothing', 'sort_order' => 2],
            ['name' => 'Pants', 'slug' => 'pants', 'type' => 'clothing', 'sort_order' => 3],
            ['name' => 'Jeans', 'slug' => 'jeans', 'type' => 'clothing', 'sort_order' => 4],
            ['name' => 'Shorts', 'slug' => 'shorts', 'type' => 'clothing', 'sort_order' => 5],
            ['name' => 'Dresses', 'slug' => 'dresses', 'type' => 'clothing', 'sort_order' => 6],
            ['name' => 'Skirts', 'slug' => 'skirts', 'type' => 'clothing', 'sort_order' => 7],
            ['name' => 'Jackets', 'slug' => 'jackets', 'type' => 'clothing', 'sort_order' => 8],
            ['name' => 'Coats', 'slug' => 'coats', 'type' => 'clothing', 'sort_order' => 9],
            ['name' => 'Sweaters', 'slug' => 'sweaters', 'type' => 'clothing', 'sort_order' => 10],
            ['name' => 'Hoodies', 'slug' => 'hoodies', 'type' => 'clothing', 'sort_order' => 11],
            ['name' => 'Activewear', 'slug' => 'activewear', 'type' => 'clothing', 'sort_order' => 12],
            ['name' => 'Swimwear', 'slug' => 'swimwear', 'type' => 'clothing', 'sort_order' => 13],
            ['name' => 'Underwear', 'slug' => 'underwear', 'type' => 'clothing', 'sort_order' => 14],
            ['name' => 'Socks', 'slug' => 'socks', 'type' => 'clothing', 'sort_order' => 15],
            ['name' => 'Shoes', 'slug' => 'shoes', 'type' => 'footwear', 'sort_order' => 16],
            ['name' => 'Accessories', 'slug' => 'accessories', 'type' => 'accessories', 'sort_order' => 17],
            ['name' => 'Bags', 'slug' => 'bags', 'type' => 'accessories', 'sort_order' => 18],
            ['name' => 'Jewelry', 'slug' => 'jewelry', 'type' => 'accessories', 'sort_order' => 19],
            ['name' => 'Hats', 'slug' => 'hats', 'type' => 'accessories', 'sort_order' => 20],
        ];

        foreach ($clothingCategories as $category) {
            Category::create($category);
        }
    }
}
