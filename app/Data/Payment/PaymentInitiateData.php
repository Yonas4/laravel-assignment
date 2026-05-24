<?php

declare(strict_types=1);

namespace App\Data\Payment;

use App\Enums\Currency;
use App\Enums\PaymentGateway;
use App\Enums\PaymentModule;
use Spatie\LaravelData\Data;

class PaymentInitiateData extends Data
{
    public function __construct(
        public PaymentGateway $gateway,
        public PaymentModule $module,
        public float $amount,
        public Currency $currency = Currency::SAR,
    ) {}
}
