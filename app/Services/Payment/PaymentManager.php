<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Enums\PaymentGateway;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\MoyasarGateway;
use App\Services\Payment\Gateways\StripeGateway;
use App\Services\Payment\Gateways\TapGateway;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * Resolve the payment gateway implementation by identifier.
     */
    public function resolve(PaymentGateway|string $gateway): PaymentGatewayInterface
    {
        if (is_string($gateway)) {
            $gateway = PaymentGateway::tryFrom($gateway);
        }

        if (! $gateway) {
            throw new InvalidArgumentException("Invalid payment gateway identifier provided.");
        }

        return match ($gateway) {
            PaymentGateway::MOYASAR => new MoyasarGateway(),
            PaymentGateway::STRIPE => new StripeGateway(),
            PaymentGateway::TAP => new TapGateway(),
        };
    }
}
