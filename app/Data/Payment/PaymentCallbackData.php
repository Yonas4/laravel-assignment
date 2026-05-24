<?php

declare(strict_types=1);

namespace App\Data\Payment;

use App\Enums\TransactionStatus;
use Spatie\LaravelData\Data;

class PaymentCallbackData extends Data
{
    public function __construct(
        public string $gateway_transaction_id,
        public TransactionStatus $status,
        public float $amount,
        public array $raw_payload,
    ) {}
}
