<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CarouselSlide;
use App\Models\HomepageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminHomepageController extends Controller
{
    // --- Sections ---

    public function sections(): JsonResponse
    {
        return response()->json(HomepageSection::orderBy('sort_order')->get());
    }

    public function updateSection(Request $request, int $id): JsonResponse
    {
        $section = HomepageSection::findOrFail($id);
        $section->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
            'settings' => 'sometimes|nullable|array',
        ]));

        return response()->json($section);
    }

    public function reorderSections(Request $request): JsonResponse
    {
        $request->validate(['sections' => 'required|array', 'sections.*.id' => 'required|integer', 'sections.*.sort_order' => 'required|integer']);

        foreach ($request->sections as $item) {
            HomepageSection::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Sections reordered.']);
    }

    // --- Carousel ---

    public function slides(): JsonResponse
    {
        return response()->json(CarouselSlide::orderBy('sort_order')->get());
    }

    public function storeSlide(Request $request): JsonResponse
    {
        $slide = CarouselSlide::create($request->validate([
            'desktop_image' => 'required|string',
            'mobile_image' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]));

        return response()->json($slide, 201);
    }

    public function updateSlide(Request $request, int $id): JsonResponse
    {
        $slide = CarouselSlide::findOrFail($id);
        $slide->update($request->validate([
            'desktop_image' => 'sometimes|string',
            'mobile_image' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]));

        return response()->json($slide);
    }

    public function destroySlide(int $id): JsonResponse
    {
        CarouselSlide::findOrFail($id)->delete();

        return response()->json(['message' => 'Slide deleted.']);
    }

    public function reorderSlides(Request $request): JsonResponse
    {
        foreach ($request->validate(['slides' => 'required|array'])['slides'] as $item) {
            CarouselSlide::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Slides reordered.']);
    }

    // --- Banners ---

    public function banners(): JsonResponse
    {
        return response()->json(Banner::orderByDesc('created_at')->get());
    }

    public function storeBanner(Request $request): JsonResponse
    {
        $banner = Banner::create($request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|string',
            'mobile_image' => 'nullable|string',
            'link_url' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]));

        return response()->json($banner, 201);
    }

    public function updateBanner(Request $request, int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        $banner->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|string',
            'mobile_image' => 'nullable|string',
            'link_url' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]));

        return response()->json($banner);
    }

    public function destroyBanner(int $id): JsonResponse
    {
        Banner::findOrFail($id)->delete();

        return response()->json(['message' => 'Banner deleted.']);
    }
}
