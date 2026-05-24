<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\PaymentTransaction;

interface PaymentTransactionRepositoryInterface
{
    /**
     * Create a new payment transaction.
     */
    public function create(array $data): PaymentTransaction;

    /**
     * Find a payment transaction by its ID.
     */
    public function find(string $id): ?PaymentTransaction;
    
    /**
     * Find a payment transaction by gateway transaction ID.
     */
    public function findByGatewayTransactionId(string $gatewayTransactionId): ?PaymentTransaction;

    /**
     * Update an existing payment transaction.
     */
    public function update(PaymentTransaction $transaction, array $data): bool;

    /**
     * Get paginated transactions for a user.
     */
    public function getForUser(string $userId, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
}
