<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['type' => 'hero_carousel', 'name' => 'Hero Carousel', 'sort_order' => 0, 'is_active' => true, 'settings' => null],
            ['type' => 'featured_products', 'name' => 'Featured Products', 'sort_order' => 1, 'is_active' => true, 'settings' => ['title' => 'Our Picks for You', 'subtitle' => 'Handcrafted with ceremonial matcha']],
            ['type' => 'categories', 'name' => 'Shop by Category', 'sort_order' => 2, 'is_active' => true, 'settings' => ['title' => 'Explore Our World']],
            ['type' => 'best_sellers', 'name' => 'Best Sellers', 'sort_order' => 3, 'is_active' => true, 'settings' => ['title' => 'Community Favorites']],
            ['type' => 'promotional_banner', 'name' => 'Promotional Banner', 'sort_order' => 4, 'is_active' => true, 'settings' => null],
            [
                'type' => 'about',
                'name' => 'About Uji-Shi',
                'sort_order' => 5,
                'is_active' => true,
                'settings' => [
                    'title' => 'Born from a Love of Japanese Tea Culture',
                    'body' => 'Uji-Shi is a premium matcha cafe celebrating the ancient art of Japanese tea. We source only the finest ceremonial and culinary grade matcha from the renowned tea fields of Uji, Kyoto.',
                    'image' => 'https://placehold.co/800x600/2c4f1e/white?text=Our+Story',
                    'cta_text' => 'Our Story',
                    'cta_url' => '/p/about',
                ],
            ],
            ['type' => 'reviews', 'name' => 'Customer Reviews', 'sort_order' => 6, 'is_active' => true, 'settings' => ['title' => 'What Our Community Says']],
            [
                'type' => 'newsletter',
                'name' => 'Newsletter',
                'sort_order' => 7,
                'is_active' => true,
                'settings' => [
                    'title' => 'Join the Matcha Community',
                    'subtitle' => 'Get exclusive offers, brewing tips, and first access to new arrivals.',
                ],
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::updateOrCreate(
                ['type' => $section['type']],
                $section
            );
        }
    }
}
