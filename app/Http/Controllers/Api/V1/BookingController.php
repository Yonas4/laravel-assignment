<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\Booking\BookingData;
use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {}

    public function store(string $id, BookingData $request): JsonResponse
    {
        $booking = $this->bookingService->createBooking(
            auth()->id(),
            $id,
            $request
        );

        return response()->json(['data' => $booking], 201);
    }
}
