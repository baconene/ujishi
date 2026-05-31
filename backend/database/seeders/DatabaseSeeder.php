<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            CarouselSeeder::class,
            HomepageSectionSeeder::class,
            BannerSeeder::class,
            SettingsSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
