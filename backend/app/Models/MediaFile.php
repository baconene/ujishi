<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    protected $table = 'media_files';

    protected $fillable = [
        'filename', 'disk', 'path', 'url', 'mime_type', 'size', 'alt', 'width', 'height',
    ];
}
