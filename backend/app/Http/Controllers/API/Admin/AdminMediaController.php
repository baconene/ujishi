<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMediaController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function index(Request $request): JsonResponse
    {
        $query = MediaFile::orderByDesc('created_at');

        if ($type = $request->get('type')) {
            $query->where('mime_type', 'like', "{$type}/%");
        }

        return response()->json($query->paginate(40));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,gif,webp|max:10240',
        ]);

        $media = $this->mediaService->upload($request->file('file'));

        return response()->json($media, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $media = MediaFile::findOrFail($id);
        $media->update($request->validate(['alt' => 'nullable|string|max:255']));

        return response()->json($media);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->mediaService->delete(MediaFile::findOrFail($id));

        return response()->json(['message' => 'Media deleted.']);
    }
}
