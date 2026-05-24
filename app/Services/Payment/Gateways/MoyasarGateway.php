<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Data\Payment\PaymentInitiateData;
use App\Data\Payment\PaymentResponseData;
use App\Enums\PaymentGateway;
use App\Enums\TransactionStatus;
use App\Services\Payment\Contracts\PaymentGatewayInterface;

class MoyasarGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'moyasar';
    }
    
    public function initiate(PaymentInitiateData $data, string $transactionId): PaymentResponseData
    {
        // Implementation stub
        return new PaymentResponseData(
            transaction_id: $transactionId,
            gateway: PaymentGateway::MOYASAR,
            amount: $data->amount,
            currency: $data->currency,
            status: TransactionStatus::PENDING,
            redirect_url: 'https://moyasar.com/checkout/' . $transactionId,
        );
    }

    public function handleCallback(array $payload): \App\Data\Payment\PaymentCallbackData
    {
        $status = match ($payload['status'] ?? '') {
            'paid' => TransactionStatus::SUCCESS,
            'failed' => TransactionStatus::FAILED,
            default => TransactionStatus::PENDING,
        };

        return new \App\Data\Payment\PaymentCallbackData(
            gateway_transaction_id: (string) ($payload['id'] ?? ''),
            status: $status,
            amount: (float) (($payload['amount'] ?? 0) / 100), // Moyasar sends amount in halalas usually
            raw_payload: $payload,
        );
    }
}
