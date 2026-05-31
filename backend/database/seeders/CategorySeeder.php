<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Matcha Drinks', 'description' => 'Premium ceremonial and culinary grade matcha beverages', 'sort_order' => 1],
            ['name' => 'Pastries & Food', 'description' => 'Artisan matcha-infused pastries and light meals', 'sort_order' => 2],
            ['name' => 'Tea Leaves', 'description' => 'Single-origin Japanese green teas and matcha powders', 'sort_order' => 3],
            ['name' => 'Merchandise', 'description' => 'Branded lifestyle products and apparel', 'sort_order' => 4],
            ['name' => 'Accessories', 'description' => 'Traditional Japanese tea ceremony tools and accessories', 'sort_order' => 5],
            ['name' => 'Gift Sets', 'description' => 'Curated matcha experience gift bundles', 'sort_order' => 6],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, [
                    'slug' => Str::slug($cat['name']),
                    'is_active' => true,
                    'image' => 'https://placehold.co/400x300/5a9e3c/white?text='.urlencode($cat['name']),
                ])
            );
        }
    }
}
