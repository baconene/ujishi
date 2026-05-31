<?php

namespace App\Http\Controllers\API\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Services\HomepageService;
use Illuminate\Http\JsonResponse;

class HomepageController extends Controller
{
    public function __construct(private HomepageService $homepageService) {}

    public function index(): JsonResponse
    {
        return response()->json($this->homepageService->assemble());
    }

    public function categories(): JsonResponse
    {
        return response()->json(
            Category::active()->whereNull('parent_id')->orderBy('sort_order')->get()
        );
    }

    public function page(string $slug): JsonResponse
    {
        return response()->json(Page::active()->where('slug', $slug)->firstOrFail());
    }
}
