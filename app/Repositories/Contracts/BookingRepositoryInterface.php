<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function create(array $data): Booking;

    public function getForUser(string $userId, int $perPage = 15): LengthAwarePaginator;

    public function findActiveForUserAndService(string $userId, string $serviceId): ?Booking;
}
