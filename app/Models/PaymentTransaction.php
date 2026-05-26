<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaymentGateway;
use App\Enums\PaymentModule;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'gateway',
    'gateway_transaction_id',
    'module',
    'amount',
    'currency',
    'status',
    'city',
    'reference',
    'idempotency_key',
    'metadata',
    'gateway_response',
    'paid_at',
    'failed_at',
])]
class PaymentTransaction extends BaseModel
{
    /** @use HasFactory<\Database\Factories\PaymentTransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway' => PaymentGateway::class,
            'module' => PaymentModule::class,
            'status' => TransactionStatus::class,
            'currency' => Currency::class,
            'metadata' => 'array',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gatewayLogs(): HasMany
    {
        return $this->hasMany(PaymentGatewayLog::class, 'transaction_id');
    }
}
