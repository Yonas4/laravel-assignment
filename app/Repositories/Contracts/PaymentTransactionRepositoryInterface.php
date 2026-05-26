<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\PaymentTransaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentTransactionRepositoryInterface
{
    public function create(array $data): PaymentTransaction;

    public function find(string $id): ?PaymentTransaction;

    public function findByGatewayTransactionId(string $gatewayTransactionId): ?PaymentTransaction;

    public function findByIdempotencyKey(string $idempotencyKey): ?PaymentTransaction;

    public function update(PaymentTransaction $transaction, array $data): bool;

    public function getForUser(string $userId, int $perPage = 15): LengthAwarePaginator;
}
