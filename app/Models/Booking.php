<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'service_id',
    'scheduled_at',
    'status',
    'notes',
])]
class Booking extends BaseModel
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
