<?php

declare(strict_types=1);

namespace App\Data\Cart;

use Spatie\LaravelData\Data;

class CartItemData extends Data
{
    public function __construct(
        public string $service_id,
        public int $quantity = 1,
    ) {}
}
