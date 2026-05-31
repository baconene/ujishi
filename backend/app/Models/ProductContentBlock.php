<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductContentBlock extends Model
{
    protected $fillable = ['product_id', 'type', 'content', 'sort_order'];

    protected $casts = ['content' => 'array'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
