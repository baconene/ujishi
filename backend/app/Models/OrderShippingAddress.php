<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShippingAddress extends Model
{
    protected $fillable = [
        'order_id', 'name', 'phone', 'address_line1', 'address_line2',
        'city', 'province', 'postal_code', 'country',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
