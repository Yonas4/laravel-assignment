<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Data\Payment\PaymentInitiateData;
use App\Data\Payment\PaymentResponseData;
use App\Enums\TransactionStatus;
use App\Repositories\Contracts\PaymentTransactionRepositoryInterface;
use App\Services\Traits\Loggable;
use Exception;
use Illuminate\Support\Str;

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
     * @return array<int, string>
     */
    public function getAvailableGateways(string $city, string $module): array
    {
        $this->logStart('get_available_gateways', ['city' => $city, 'module' => $module]);

        try {
            $rules = config('payments.rules', []);
            $available = [];

            foreach ($rules as $gateway => $rule) {
                $cities = $rule['cities'] ?? [];
                $modules = $rule['modules'] ?? [];

                $cityMatches = in_array('*', $cities) || in_array($city, $cities);
                $moduleMatches = in_array('*', $modules) || in_array($module, $modules);

                if ($cityMatches && $moduleMatches) {
                    $available[] = $gateway;
                }
            }

            $this->logSuccess('get_available_gateways', ['gateways' => $available]);

            return $available;
        } catch (\Throwable $e) {
            $this->logFailure('get_available_gateways', $e, ['city' => $city, 'module' => $module]);
            throw $e;
        }
    }

    public function initiatePayment(PaymentInitiateData $data, string $userId): PaymentResponseData
    {
        $this->logStart('initiate_payment', ['user_id' => $userId, 'gateway' => $data->gateway->value]);

        try {
            // First create a pending transaction in our database
            $transaction = $this->transactionRepository->create([
                'user_id' => $userId,
                'gateway' => $data->gateway->value,
                'module' => $data->module->value,
                'amount' => $data->amount,
                'currency' => $data->currency->value,
                'status' => TransactionStatus::PENDING->value,
            ]);

            // Resolve the correct gateway
            $gateway = $this->paymentManager->resolve($data->gateway);

            // Initiate payment via gateway
            $response = $gateway->initiate($data, $transaction->id);

            // Update transaction with gateway transaction ID and payload
            $this->transactionRepository->update($transaction, [
                'gateway_transaction_id' => $response->transaction_id,
            ]);

            $this->logSuccess('initiate_payment', ['transaction_id' => $transaction->id]);

            return $response;
        } catch (\Throwable $e) {
            $this->logFailure('initiate_payment', $e, ['user_id' => $userId]);
            throw $e;
        }
    }

    public function handleCallback(string $gatewayName, array $payload): void
    {
        $this->logStart('handle_payment_callback', ['gateway' => $gatewayName]);

        try {
            $gateway = $this->paymentManager->resolve($gatewayName);
            $callbackData = $gateway->handleCallback($payload);

            $transaction = $this->transactionRepository->findByGatewayTransactionId($callbackData->gateway_transaction_id);

            if (! $transaction) {
                // If the transaction is not found, log it and ignore. In a real system you might want to handle it differently.
                $this->logFailure('handle_payment_callback', new Exception('Transaction not found'), [
                    'gateway_transaction_id' => $callbackData->gateway_transaction_id
                ]);
                return;
            }

            // Idempotency check: if already processed, return
            if (in_array($transaction->status, [TransactionStatus::SUCCESS, TransactionStatus::FAILED, TransactionStatus::REFUNDED])) {
                $this->logSuccess('handle_payment_callback_idempotent', ['transaction_id' => $transaction->id]);
                return;
            }

            $updateData = [
                'status' => $callbackData->status->value,
                'gateway_response' => $callbackData->raw_payload,
            ];

            if ($callbackData->status === TransactionStatus::SUCCESS) {
                $updateData['paid_at'] = now();
            }

            $this->transactionRepository->update($transaction, $updateData);

            $this->logSuccess('handle_payment_callback', ['transaction_id' => $transaction->id, 'status' => $callbackData->status->value]);
        } catch (\Throwable $e) {
            $this->logFailure('handle_payment_callback', $e, ['gateway' => $gatewayName]);
            throw $e;
        }
    }

    public function getTransactionsForUser(string $userId, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->transactionRepository->getForUser($userId, $perPage);
    }
}
