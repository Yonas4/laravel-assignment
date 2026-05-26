<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'cart_id',
    'itemable_type',
    'itemable_id',
    'item_type',
    'name',
    'quantity',
    'unit_price',
])]
class CartItem extends BaseModel
{
    /** @use HasFactory<\Database\Factories\CartItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
