<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaymentGateway;
use App\Enums\PaymentModule;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'gateway',
    'gateway_transaction_id',
    'module',
    'amount',
    'currency',
    'status',
    'gateway_payload',
    'gateway_response',
    'paid_at'
])]
class PaymentTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentTransactionFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway' => PaymentGateway::class,
            'module' => PaymentModule::class,
            'status' => TransactionStatus::class,
            'currency' => Currency::class,
            'gateway_payload' => 'array',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
