<?php

declare(strict_types=1);

namespace App\Data\Payment;

use App\Enums\Currency;
use App\Enums\PaymentGateway;
use App\Enums\TransactionStatus;
use Spatie\LaravelData\Data;

class PaymentResponseData extends Data
{
    public function __construct(
        public string $transaction_id,
        public PaymentGateway $gateway,
        public float $amount,
        public Currency $currency,
        public TransactionStatus $status,
        public ?string $redirect_url = null,
    ) {}
}
