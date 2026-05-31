<?php

namespace Database\Seeders;

use App\Models\CarouselSlide;
use Illuminate\Database\Seeder;

class CarouselSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'desktop_image' => 'https://placehold.co/1920x800/2c4f1e/white?text=Premium+Matcha+Experience',
                'mobile_image' => 'https://placehold.co/768x600/2c4f1e/white?text=Premium+Matcha',
                'title' => 'Experience the Art of Matcha',
                'subtitle' => 'Ceremonial grade matcha from the hills of Uji, Kyoto',
                'cta_text' => 'Shop Now',
                'cta_url' => '/products',
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'desktop_image' => 'https://placehold.co/1920x800/366122/white?text=New+Season+Collection',
                'mobile_image' => 'https://placehold.co/768x600/366122/white?text=New+Collection',
                'title' => 'Spring 2024 Collection',
                'subtitle' => 'Sakura matcha, limited edition blends & seasonal pastries',
                'cta_text' => 'Explore',
                'cta_url' => '/products?featured=true',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'desktop_image' => 'https://placehold.co/1920x800/447d2d/white?text=Gift+Sets+Available',
                'mobile_image' => 'https://placehold.co/768x600/447d2d/white?text=Gift+Sets',
                'title' => 'The Perfect Matcha Gift',
                'subtitle' => 'Curated gift sets for the matcha lover in your life',
                'cta_text' => 'View Gift Sets',
                'cta_url' => '/products?category=gift-sets',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($slides as $slide) {
            CarouselSlide::updateOrCreate(
                ['title' => $slide['title']],
                $slide
            );
        }
    }
}
