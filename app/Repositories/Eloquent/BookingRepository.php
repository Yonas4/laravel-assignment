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
}
