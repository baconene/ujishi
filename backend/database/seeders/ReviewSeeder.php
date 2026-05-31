<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@ujishi.ph')->first();
        if (! $customer) {
            return;
        }

        $reviews = [
            ['product_sku' => 'UJI-DRK-001', 'rating' => 5, 'title' => 'Absolutely divine!', 'body' => 'The ceremonial matcha latte is the best I have ever tasted outside Japan. The oat milk foam is so creamy and the matcha is perfectly balanced — not bitter at all. Will be coming back every week!'],
            ['product_sku' => 'UJI-TEA-001', 'rating' => 5, 'title' => 'Premium quality matcha', 'body' => 'This matcha powder is incredibly smooth and vibrant green. You can really taste the difference from supermarket brands. Perfect for my morning ritual.'],
            ['product_sku' => 'UJI-PST-001', 'rating' => 5, 'title' => 'Best dessert in Metro Manila', 'body' => 'The matcha tiramisu is a game changer. The layers are perfectly balanced, not too sweet, and the matcha flavor shines through every bite.'],
            ['product_sku' => 'UJI-GFT-001', 'rating' => 4, 'title' => 'Great gift set', 'body' => 'Bought this as a gift for my mom who loves tea. She absolutely loved it! The chasen and chawan are beautiful quality. Packaging was also very premium.'],
            ['product_sku' => 'UJI-DRK-002', 'rating' => 4, 'title' => 'Unique and delicious', 'body' => 'Never tried hojicha before and now I am obsessed. The roasted flavor is so comforting and it pairs perfectly with the oat milk. Great for afternoons!'],
        ];

        foreach ($reviews as $reviewData) {
            $product = Product::where('sku', $reviewData['product_sku'])->first();
            if (! $product) {
                continue;
            }

            Review::updateOrCreate(
                ['user_id' => $customer->id, 'product_id' => $product->id],
                [
                    'rating' => $reviewData['rating'],
                    'title' => $reviewData['title'],
                    'body' => $reviewData['body'],
                    'is_approved' => true,
                ]
            );
        }
    }
}
