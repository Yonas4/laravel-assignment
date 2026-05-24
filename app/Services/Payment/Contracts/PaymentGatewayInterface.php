<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Get the identifier of the payment gateway.
     */
    public function getIdentifier(): string;
    
    /**
     * Initiate a payment transaction.
     */
    public function initiate(\App\Data\Payment\PaymentInitiateData $data, string $transactionId): \App\Data\Payment\PaymentResponseData;

    /**
     * Handle webhook/callback payload.
     */
    public function handleCallback(array $payload): \App\Data\Payment\PaymentCallbackData;
}
