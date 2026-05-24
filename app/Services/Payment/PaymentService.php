<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Data\Payment\PaymentInitiateData;
use App\Data\Payment\PaymentResponseData;
use App\Enums\TransactionStatus;
use App\Models\PaymentTransaction;
use App\Repositories\Contracts\PaymentTransactionRepositoryInterface;
use App\Services\Traits\Loggable;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    use Loggable;

    public function __construct(
        private readonly PaymentManager $paymentManager,
        private readonly PaymentTransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Get available payment gateways for a specific city and module.
     *
     * @return array<int, array{key: string, label: string}>
     */
    public function getAvailableGateways(string $city, string $module): array
    {
        $this->logPayment('get_available_gateways', ['city' => $city, 'module' => $module]);

        try {
            $rules = config('payments.rules', []);
            $available = [];

            foreach ($rules as $gateway => $rule) {
                $cities = $rule['cities'] ?? [];
                $modules = $rule['modules'] ?? [];

                $cityMatches = in_array('*', $cities) || in_array($city, $cities);
                $moduleMatches = in_array('*', $modules) || in_array($module, $modules);

                if ($cityMatches && $moduleMatches) {
                    $available[] = [
                        'key' => $gateway,
                        'label' => ucfirst($gateway),
                    ];
                }
            }

            $this->logPayment('get_available_gateways_success', ['gateways' => $available]);

            return $available;
        } catch (\Throwable $e) {
            $this->logPaymentFailure('get_available_gateways', $e, ['city' => $city, 'module' => $module]);
            throw $e;
        }
    }

    public function initiatePayment(PaymentInitiateData $data, string $userId): PaymentResponseData
    {
        $this->logPayment('initiate_payment', ['user_id' => $userId, 'gateway' => $data->gateway->value]);

        try {
            // IDEMPOTENCY CHECK — must be the FIRST thing
            $existing = $this->transactionRepository->findByIdempotencyKey($data->idempotency_key);
            if ($existing) {
                $this->logPayment('initiate_payment_idempotent', ['transaction_id' => $existing->id]);
                return new PaymentResponseData(
                    transaction_id: $existing->id,
                    gateway: $existing->gateway,
                    amount: (float) $existing->amount,
                    currency: $existing->currency,
                    status: $existing->status,
                );
            }

            // Validate gateway is available for the given city and module
            $availableGateways = $this->getAvailableGateways($data->city, $data->module->value);
            $gatewayKeys = array_column($availableGateways, 'key');

            if (!in_array($data->gateway->value, $gatewayKeys)) {
                throw ValidationException::withMessages([
                    'gateway' => ['The selected gateway is not available for your city and module.'],
                ]);
            }

            // Create a pending transaction
            $transaction = $this->transactionRepository->create([
                'user_id' => $userId,
                'gateway' => $data->gateway->value,
                'module' => $data->module->value,
                'amount' => $data->amount,
                'currency' => $data->currency->value,
                'city' => $data->city,
                'status' => TransactionStatus::PENDING->value,
                'idempotency_key' => $data->idempotency_key,
            ]);

            // Resolve the correct gateway
            $gateway = $this->paymentManager->resolve($data->gateway);

            // Initiate payment via gateway
            $response = $gateway->initiate($data, $transaction->id);

            // Update transaction with gateway transaction ID
            $this->transactionRepository->update($transaction, [
                'gateway_transaction_id' => $response->transaction_id,
            ]);

            $this->logPayment('initiate_payment_success', ['transaction_id' => $transaction->id]);

            return $response;
        } catch (\Throwable $e) {
            $this->logPaymentFailure('initiate_payment', $e, ['user_id' => $userId]);
            throw $e;
        }
    }

    public function handleCallback(string $gatewayName, array $payload): void
    {
        $this->logPayment('handle_payment_callback', ['gateway' => $gatewayName]);

        try {
            $gateway = $this->paymentManager->resolve($gatewayName);
            $callbackData = $gateway->handleCallback($payload);

            $transaction = $this->transactionRepository->findByGatewayTransactionId($callbackData->gateway_transaction_id);

            if (! $transaction) {
                $this->logPaymentFailure('handle_payment_callback', new Exception('Transaction not found'), [
                    'gateway_transaction_id' => $callbackData->gateway_transaction_id,
                ]);
                return;
            }

            // Terminal state check — prevent duplicate callback processing
            if ($transaction->status !== TransactionStatus::PENDING) {
                $this->logPayment('handle_payment_callback_idempotent', ['transaction_id' => $transaction->id]);
                return;
            }

            $updateData = [
                'status' => $callbackData->status->value,
                'gateway_response' => $callbackData->raw_payload,
            ];

            if ($callbackData->status === TransactionStatus::SUCCESS) {
                $updateData['paid_at'] = now();
            }

            if ($callbackData->status === TransactionStatus::FAILED) {
                $updateData['failed_at'] = now();
            }

            $this->transactionRepository->update($transaction, $updateData);

            $this->logPayment('handle_payment_callback_success', [
                'transaction_id' => $transaction->id,
                'status' => $callbackData->status->value,
            ]);
        } catch (\Throwable $e) {
            $this->logPaymentFailure('handle_payment_callback', $e, ['gateway' => $gatewayName]);
            throw $e;
        }
    }

    public function getTransactionsForUser(string $userId, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->transactionRepository->getForUser($userId, $perPage);
    }

    public function getTransactionForUser(string $id, string $userId): ?PaymentTransaction
    {
        $transaction = $this->transactionRepository->find($id);

        if (!$transaction || $transaction->user_id !== $userId) {
            return null;
        }

        return $transaction;
    }

    /**
     * Log to the dedicated payment channel.
     */
    private function logPayment(string $operation, array $context = []): void
    {
        Log::channel('payment')->info("Payment: {$operation}", $context);
    }

    private function logPaymentFailure(string $operation, \Throwable $e, array $context = []): void
    {
        Log::channel('payment')->error("Payment Failed: {$operation}", array_merge($context, [
            'error' => $e->getMessage(),
        ]));
    }
}
