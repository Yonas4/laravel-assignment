<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PaymentGatewayLog intentionally extends Model directly (NOT BaseModel)
 * because logs are immutable — they must never be soft-deleted.
 */
class PaymentGatewayLog extends Model
{
    use HasUlids;

    protected $fillable = [
        'transaction_id',
        'gateway',
        'event',
        'direction',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }
}
