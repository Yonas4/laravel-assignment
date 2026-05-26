<?php

declare(strict_types=1);

namespace App\Data\Cart;

use Spatie\LaravelData\Data;

class CartItemData extends Data
{
    public function __construct(
        public string $item_id,
        public string $item_type = 'service',
        public int $quantity = 1,
    ) {}
}
