<?php

declare(strict_types=1);

namespace App\Data\Payment;

use Spatie\LaravelData\Data;

class GatewayQueryData extends Data
{
    public function __construct(
        public string $city,
        public string $module,
    ) {}
}
