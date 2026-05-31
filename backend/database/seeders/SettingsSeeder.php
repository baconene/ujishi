<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'store_name', 'value' => 'Uji-Shi Matcha Cafe', 'group' => 'general'],
            ['key' => 'store_tagline', 'value' => 'Premium Japanese Matcha Experience', 'group' => 'general'],
            ['key' => 'store_email', 'value' => 'hello@ujishi.ph', 'group' => 'general'],
            ['key' => 'store_phone', 'value' => '+63 917 123 4567', 'group' => 'general'],
            ['key' => 'store_address', 'value' => 'BGC, Taguig City, Metro Manila, Philippines', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'PHP', 'group' => 'general'],
            ['key' => 'currency_symbol', 'value' => '₱', 'group' => 'general'],
            ['key' => 'free_shipping_threshold', 'value' => '1500', 'group' => 'shipping'],
            ['key' => 'default_shipping_fee', 'value' => '150', 'group' => 'shipping'],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/ujishi', 'group' => 'social'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/ujishi', 'group' => 'social'],
            ['key' => 'tiktok_url', 'value' => 'https://tiktok.com/@ujishi', 'group' => 'social'],
            ['key' => 'meta_title', 'value' => 'Uji-Shi Matcha Cafe | Premium Japanese Matcha', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Experience premium ceremonial and culinary grade matcha from Uji, Kyoto. Shop drinks, pastries, tea leaves, and accessories at Uji-Shi Matcha Cafe.', 'group' => 'seo'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
