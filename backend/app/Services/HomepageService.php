<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\CarouselSlide;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\Review;

class HomepageService
{
    public function assemble(): array
    {
        $sections = HomepageSection::active()->get();

        return $sections->map(fn (HomepageSection $section) => [
            'id' => $section->id,
            'type' => $section->type,
            'name' => $section->name,
            'settings' => $section->settings,
            'data' => $this->loadSectionData($section),
        ])->values()->toArray();
    }

    private function loadSectionData(HomepageSection $section): mixed
    {
        return match ($section->type) {
            'hero_carousel' => CarouselSlide::active()->get(),
            'featured_products' => Product::featured()->with('category', 'images')->limit(8)->get(),
            'best_sellers' => Product::bestSellers()->with('category', 'images')->limit(8)->get(),
            'categories' => Category::active()->whereNull('parent_id')->orderBy('sort_order')->get(),
            'promotional_banner' => Banner::active()->first(),
            'about' => $section->settings,
            'reviews' => Review::approved()->with('user')->latest()->limit(6)->get(),
            'newsletter' => $section->settings,
            default => null,
        };
    }
}
