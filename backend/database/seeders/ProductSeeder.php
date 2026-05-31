<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductContentBlock;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $drinksCat = Category::where('slug', 'matcha-drinks')->first();
        $pastriesCat = Category::where('slug', 'pastries-food')->first();
        $teaCat = Category::where('slug', 'tea-leaves')->first();
        $giftCat = Category::where('slug', 'gift-sets')->first();
        $accCat = Category::where('slug', 'accessories')->first();

        $products = [
            [
                'name' => 'Ceremonial Grade Matcha Latte',
                'sku' => 'UJI-DRK-001',
                'price' => 220,
                'sale_price' => null,
                'description' => 'Our signature matcha latte made with 100% ceremonial grade matcha from Uji, Kyoto. Whisked to perfection with oat milk foam.',
                'category_id' => $drinksCat?->id,
                'is_featured' => true,
                'is_best_seller' => true,
                'stock' => 99,
            ],
            [
                'name' => 'Iced Hojicha Latte',
                'sku' => 'UJI-DRK-002',
                'price' => 195,
                'sale_price' => 175,
                'description' => 'Roasted hojicha blended with cold oat milk over ice. Earthy, warming, and refreshing.',
                'category_id' => $drinksCat?->id,
                'is_featured' => true,
                'is_best_seller' => false,
                'stock' => 99,
            ],
            [
                'name' => 'Matcha Tiramisu',
                'sku' => 'UJI-PST-001',
                'price' => 280,
                'sale_price' => null,
                'description' => 'A Japanese-Italian fusion dessert. Ladyfingers soaked in matcha, layered with mascarpone cream and dusted with ceremonial matcha.',
                'category_id' => $pastriesCat?->id,
                'is_featured' => true,
                'is_best_seller' => true,
                'stock' => 30,
            ],
            [
                'name' => 'Matcha Croissant',
                'sku' => 'UJI-PST-002',
                'price' => 145,
                'sale_price' => null,
                'description' => 'Flaky butter croissant with matcha cream filling and matcha glaze. Baked fresh daily.',
                'category_id' => $pastriesCat?->id,
                'is_featured' => false,
                'is_best_seller' => true,
                'stock' => 50,
            ],
            [
                'name' => 'Uji Ceremonial Matcha 30g',
                'sku' => 'UJI-TEA-001',
                'price' => 850,
                'sale_price' => null,
                'description' => 'Single-origin ceremonial grade matcha from Uji, Kyoto. First harvest spring 2024. Deep umami, vibrant green color.',
                'category_id' => $teaCat?->id,
                'is_featured' => true,
                'is_best_seller' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Culinary Grade Matcha 100g',
                'sku' => 'UJI-TEA-002',
                'price' => 490,
                'sale_price' => 420,
                'description' => 'Perfect for baking, smoothies, and lattes. Vibrant green and packed with antioxidants.',
                'category_id' => $teaCat?->id,
                'is_featured' => false,
                'is_best_seller' => true,
                'stock' => 150,
            ],
            [
                'name' => 'Matcha Starter Kit',
                'sku' => 'UJI-GFT-001',
                'price' => 1890,
                'sale_price' => 1650,
                'description' => 'Everything you need to start your matcha journey at home. Includes ceremonial matcha, chasen, chawan, and chashaku.',
                'category_id' => $giftCat?->id,
                'is_featured' => true,
                'is_best_seller' => false,
                'stock' => 25,
            ],
            [
                'name' => 'Traditional Chasen (Tea Whisk)',
                'sku' => 'UJI-ACC-001',
                'price' => 480,
                'sale_price' => null,
                'description' => 'Hand-crafted bamboo chasen with 80 tines. Essential tool for whisking the perfect matcha.',
                'category_id' => $accCat?->id,
                'is_featured' => false,
                'is_best_seller' => false,
                'stock' => 40,
            ],
            [
                'name' => 'Matcha Chawan (Tea Bowl)',
                'sku' => 'UJI-ACC-002',
                'price' => 650,
                'sale_price' => null,
                'description' => 'Authentic Japanese pottery chawan in rustic wabi-sabi style. Handmade in Kyoto.',
                'category_id' => $accCat?->id,
                'is_featured' => false,
                'is_best_seller' => false,
                'stock' => 20,
            ],
            [
                'name' => 'Sakura Matcha Latte',
                'sku' => 'UJI-DRK-003',
                'price' => 245,
                'sale_price' => null,
                'description' => 'Limited edition seasonal drink. Ceremonial matcha with salted sakura, steamed milk, and cherry blossom foam.',
                'category_id' => $drinksCat?->id,
                'is_featured' => true,
                'is_best_seller' => false,
                'stock' => 99,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::updateOrCreate(
                ['sku' => $productData['sku']],
                array_merge($productData, [
                    'slug' => Str::slug($productData['name']),
                    'is_active' => true,
                    'thumbnail' => 'https://placehold.co/600x600/5a9e3c/white?text='.urlencode($productData['name']),
                    'seo_title' => $productData['name'].' | Uji-Shi Matcha Cafe',
                    'seo_description' => $productData['description'],
                ])
            );

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => 0],
                [
                    'path' => "products/{$product->sku}-1.jpg",
                    'url' => "https://placehold.co/800x800/5a9e3c/white?text=".urlencode($product->name),
                    'alt' => $product->name,
                ]
            );

            ProductContentBlock::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => 0],
                [
                    'type' => 'rich_text',
                    'content' => [
                        'html' => "<h2>About this product</h2><p>{$product->description}</p>",
                    ],
                ]
            );

            ProductContentBlock::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => 1],
                [
                    'type' => 'ingredients',
                    'content' => [
                        'items' => [
                            ['name' => 'Ceremonial Grade Matcha', 'amount' => '2g'],
                            ['name' => 'Oat Milk', 'amount' => '200ml'],
                            ['name' => 'Hot Water', 'amount' => '30ml'],
                        ],
                    ],
                ]
            );
        }
    }
}
