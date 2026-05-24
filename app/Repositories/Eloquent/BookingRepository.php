<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    public function getForUser(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Booking::where('user_id', $userId)
            ->orderBy('scheduled_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find an active (pending or confirmed) booking for a user and service.
     */
    public function findActiveForUserAndService(string $userId, string $serviceId): ?Booking
    {
        return Booking::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();
    }
}
