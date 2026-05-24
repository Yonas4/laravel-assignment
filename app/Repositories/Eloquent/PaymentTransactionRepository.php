<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\PaymentTransaction;
use App\Repositories\Contracts\PaymentTransactionRepositoryInterface;

class PaymentTransactionRepository implements PaymentTransactionRepositoryInterface
{
    public function create(array $data): PaymentTransaction
    {
        return PaymentTransaction::create($data);
    }

    public function find(string $id): ?PaymentTransaction
    {
        return PaymentTransaction::find($id);
    }
    
    public function findByGatewayTransactionId(string $gatewayTransactionId): ?PaymentTransaction
    {
        return PaymentTransaction::where('gateway_transaction_id', $gatewayTransactionId)->first();
    }

    public function update(PaymentTransaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function getForUser(string $userId, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return PaymentTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
