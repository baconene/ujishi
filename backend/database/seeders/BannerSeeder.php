<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::updateOrCreate(
            ['title' => 'Free Delivery on Orders Over ₱1,500'],
            [
                'title' => 'Free Delivery on Orders Over ₱1,500',
                'image' => 'https://placehold.co/1400x300/5a9e3c/white?text=Free+Delivery+on+Orders+Over+₱1500',
                'mobile_image' => 'https://placehold.co/768x250/5a9e3c/white?text=Free+Delivery+₱1500',
                'link_url' => '/products',
                'description' => 'Enjoy free nationwide delivery when you spend ₱1,500 or more.',
                'is_active' => true,
            ]
        );
    }
}
