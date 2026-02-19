<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmBookingRequest;
use App\Http\Requests\LockSeatsRequest;
use App\Http\Resources\OrderResource;
use App\Models\Event;
use App\Models\Order;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function lockSeats(LockSeatsRequest $request, string $slug): JsonResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        try {
            $order = $this->bookingService->lockSeats(
                $request->user(),
                $event,
                $request->validated('seat_ids'),
            );
        } catch (BookingException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_CONFLICT,
            );
        }

        $order->load('items');

        return new OrderResource($order)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function confirm(ConfirmBookingRequest $request, Order $order): OrderResource|JsonResponse
    {
        try {
            $order = $this->bookingService->confirmBooking(
                $request->user(),
                $order,
                PaymentMethod::from($request->validated('payment_method')),
            );
        } catch (BookingException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_CONFLICT,
            );
        }

        $order->load('payment');

        return new OrderResource($order);
    }
}
