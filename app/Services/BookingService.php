<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Booking\BookingData;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Services\Traits\Loggable;
use Illuminate\Validation\ValidationException;

class BookingService
{
    use Loggable;

    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly ServiceRepositoryInterface $serviceRepository,
        private readonly SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    public function createBooking(string $userId, string $serviceId, BookingData $data): Booking
    {
        $this->logStart('create_booking', ['user_id' => $userId, 'service_id' => $serviceId]);

        try {
            // Check active subscription
            $activeSubscription = $this->subscriptionRepository->findActiveForUser($userId);
            if (!$activeSubscription) {
                throw ValidationException::withMessages([
                    'subscription' => ['User must have an active subscription to book services.'],
                ]);
            }

            // Check service existence and availability
            $service = $this->serviceRepository->findById($serviceId);
            if (!$service || !$service->is_available) {
                throw ValidationException::withMessages([
                    'service_id' => ['The selected service is not available.'],
                ]);
            }

            $booking = $this->bookingRepository->create([
                'user_id' => $userId,
                'service_id' => $serviceId,
                'scheduled_at' => $data->scheduled_at,
                'status' => BookingStatus::CONFIRMED->value,
            ]);

            $this->logSuccess('create_booking', ['booking_id' => $booking->id]);

            return $booking;
        } catch (\Throwable $e) {
            $this->logFailure('create_booking', $e, ['user_id' => $userId]);
            throw $e;
        }
    }
}
