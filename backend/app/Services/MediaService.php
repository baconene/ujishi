<?php

namespace App\Services;

use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    public function upload(UploadedFile $file, string $folder = 'media'): MediaFile
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = "{$folder}/{$filename}";

        $image = Image::read($file);
        $width = $image->width();
        $height = $image->height();

        if ($width > 1920) {
            $image->scaleDown(width: 1920);
        }

        Storage::disk('public')->put($path, $image->toJpeg(85));

        $baseUrl = rtrim(config('app.media_url') ?: config('app.url'), '/');
        $url = "{$baseUrl}/storage/{$path}";

        return MediaFile::create([
            'filename' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $path,
            'url' => $url,
            'mime_type' => $file->getMimeType(),
            'size' => Storage::disk('public')->size($path),
            'width' => $image->width(),
            'height' => $image->height(),
        ]);
    }

    public function delete(MediaFile $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }
}
