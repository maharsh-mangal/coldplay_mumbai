<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

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
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

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
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'redirect' => route('checkout', $order),
        ]);
    }

    public function checkout(Order $order): Response|RedirectResponse
    {
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            abort(403);
        }

        if (! $order->isPending()) {
            return redirect()->route('confirmation', $order);
        }

        if ($order->isExpired()) {
            return redirect()->route('events.show', $order->event->slug)
                ->withErrors(['booking' => 'Your seat hold has expired. Please try again.']);
        }

        $order->load(['event.tour', 'event.venue.city', 'items.seat.section']);

        $addresses = $user->addresses()->get()->map(fn ($address): array => [
            'id' => $address->id,
            'label' => $address->label,
            'full_address' => $address->fullAddress(),
            'is_default' => $address->is_default,
        ]);

        return Inertia::render('Checkout', [
            'order' => (new OrderResource($order))->resolve(),
            'addresses' => $addresses,
            'paymentMethods' => collect(PaymentMethod::cases())->map(fn (PaymentMethod $m): array => [
                'value' => $m->value,
                'label' => match ($m) {
                    PaymentMethod::Upi => 'UPI',
                    PaymentMethod::CreditCard => 'Credit Card',
                    PaymentMethod::DebitCard => 'Debit Card',
                    PaymentMethod::NetBanking => 'Net Banking',
                },
            ]),
        ]);
    }

    public function confirm(ConfirmBookingRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->bookingService->confirmBooking(
                $request->user(),
                $order,
                PaymentMethod::from($request->validated('payment_method')),
            );
        } catch (BookingException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        return redirect()->route('confirmation', $order);
    }

    public function confirmation(Order $order): Response
    {
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            abort(403);
        }

        $order->load(['event.tour', 'event.venue.city', 'items.seat.section', 'payment']);

        return Inertia::render('Confirmation', [
            'order' => (new OrderResource($order))->resolve(),
        ]);
    }
}
