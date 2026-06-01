<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductContentBlock;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category', 'images')->orderByDesc('created_at');

        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateProduct($request);
        $data['slug'] = Str::slug($data['name']);

        $product = Product::create($data);
        $this->syncImages($product, $request->get('images', []));
        $this->syncContentBlocks($product, $request->get('content_blocks', []));

        return response()->json($product->load('images', 'contentBlocks', 'category'), 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(
            Product::with('category', 'images', 'contentBlocks')->findOrFail($id)
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $data = $this->validateProduct($request, $id);

        if (empty($data['slug']) || $data['slug'] !== $product->slug) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);
        $this->syncImages($product, $request->get('images', []));
        $this->syncContentBlocks($product, $request->get('content_blocks', []));

        return response()->json($product->fresh(['images', 'contentBlocks', 'category']));
    }

    public function destroy(int $id): JsonResponse
    {
        Product::findOrFail($id)->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    private function validateProduct(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'sku' => "required|string|unique:products,sku,{$id}",
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'stock' => 'integer|min:0',
            'is_featured' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_active' => 'boolean',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:500',
            'weight' => 'nullable|numeric|min:0',
        ]);
    }

    private function syncImages(Product $product, array $images): void
    {
        $product->images()->delete();
        $order = 0;
        foreach ($images as $img) {
            if (empty($img['url'])) continue;
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $img['path'] ?? '',
                'url' => $img['url'],
                'alt' => $img['alt'] ?? $product->name,
                'sort_order' => $order++,
            ]);
        }
    }

    private function syncContentBlocks(Product $product, array $blocks): void
    {
        $product->contentBlocks()->delete();
        foreach ($blocks as $i => $block) {
            ProductContentBlock::create([
                'product_id' => $product->id,
                'type' => $block['type'],
                'content' => $block['content'],
                'sort_order' => $i,
            ]);
        }
    }
}
