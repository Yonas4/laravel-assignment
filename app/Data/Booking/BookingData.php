<?php

declare(strict_types=1);

namespace App\Data\Booking;

use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Data;

class BookingData extends Data
{
    public function __construct(
        #[Date, After('now')]
        public string $scheduled_at,
    ) {}
}
