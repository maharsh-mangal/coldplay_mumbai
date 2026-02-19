<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SeatStatus;
use App\Exceptions\BookingException;
use App\Mail\BookingConfirmation;
use App\Models\Event;
use App\Models\Order;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class BookingService
{
    /**
     * Lock hold duration in minutes.
     * Seats return to available pool after this window.
     */
    private const int LOCK_MINUTES = 2;

    /**
     * Lock seats for a user and create a pending order.
     *
     * Idempotency: if the same user already holds these exact seats with a
     * pending order, we return that order instead of creating a duplicate.
     *
     * @throws Throwable
     */
    public function lockSeats(User $user, Event $event, array $seatIds): Order
    {
        if (empty($seatIds)) {
            throw new BookingException('No seats selected.');
        }

        return DB::transaction(function () use ($user, $event, $seatIds): Order {
            // 1. Acquire row-level locks on requested seats
            $seats = Seat::whereIn('id', $seatIds)
                ->lockForUpdate()
                ->get();

            // 2. Validate all seats belong to this event
            $invalidSeats = $seats->where('event_id', '!=', $event->id);
            if ($invalidSeats->isNotEmpty()) {
                throw new BookingException('Some seats do not belong to this event.');
            }

            // 3. Idempotency — return existing order if user already holds these seats
            $existingOrder = $this->findExistingOrder($user, $event, $seatIds);
            if ($existingOrder) {
                return $existingOrder;
            }

            // 4. Release any expired locks (treat them as available)
            $seats->each(function (Seat $seat): void {
                if ($seat->isLockExpired()) {
                    $seat->update([
                        'status' => SeatStatus::Available,
                        'locked_by' => null,
                        'locked_until' => null,
                    ]);
                    $seat->refresh();
                }
            });

            // 5. Check all seats are available
            $unavailable = $seats->filter(fn (Seat $seat): bool => ! $seat->isAvailable());
            if ($unavailable->isNotEmpty()) {
                throw new BookingException('One or more selected seats are no longer available.');
            }

            // 6. Lock seats
            $lockedUntil = now()->addMinutes(self::LOCK_MINUTES);
            $seats->each(fn (Seat $seat) => $seat->update([
                'status' => SeatStatus::Locked,
                'locked_by' => $user->id,
                'locked_until' => $lockedUntil,
            ]));

            // 7. Calculate pricing
            $seats->load('section');
            $subtotal = $seats->sum(fn (Seat $seat): int => $seat->section->price_in_paisa);
            $tax = Order::calculateTax($subtotal);

            // 8. Create order with items
            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'status' => OrderStatus::Pending,
                'subtotal_in_paisa' => $subtotal,
                'tax_in_paisa' => $tax,
                'total_in_paisa' => $subtotal + $tax,
                'expires_at' => $lockedUntil,
            ]);

            foreach ($seats as $seat) {
                $order->items()->create([
                    'seat_id' => $seat->id,
                    'price_in_paisa' => $seat->section->price_in_paisa,
                ]);
            }

            return $order->load('items');
        });
    }

    /**
     * Confirm a pending order — mark seats booked, create payment, queue email.
     *
     * Payment is simulated (no real gateway) — generates a fake transaction ID.
     * In production, this would integrate with Razorpay/Paytm and handle
     * webhook callbacks for async payment confirmation.
     */
    public function confirmBooking(User $user, Order $order, PaymentMethod $method): Order
    {
        if ($order->user_id !== $user->id) {
            throw new BookingException('This order does not belong to you.');
        }

        if ($order->isExpired()) {
            throw new BookingException('This order has expired.');
        }

        if (! $order->isPending()) {
            throw new BookingException('This order is not in a confirmable state.');
        }

        return DB::transaction(function () use ($user, $order, $method): Order {
            // 1. Confirm order
            $order->update([
                'status' => OrderStatus::Confirmed,
                'confirmed_at' => now(),
            ]);

            // 2. Book seats and clear locks
            $seatIds = $order->items()->pluck('seat_id');
            Seat::whereIn('id', $seatIds)->update([
                'status' => SeatStatus::Booked,
                'locked_by' => null,
                'locked_until' => null,
            ]);

            // 3. Create payment record (simulated)
            $order->payments()->create([
                'amount_in_paisa' => $order->total_in_paisa,
                'method' => $method,
                'status' => PaymentStatus::Completed,
                'transaction_id' => 'TXN_'.Str::upper(Str::random(12)),
                'paid_at' => now(),
            ]);

            // 4. Queue confirmation email
            $order->load(['event.tour', 'event.venue', 'items.seat.section', 'user']);
            Mail::to($user)->queue(new BookingConfirmation($order));

            return $order;
        });
    }

    /**
     * Find an existing pending order for the same user + seats (idempotency).
     */
    private function findExistingOrder(User $user, Event $event, array $seatIds): ?Order
    {
        sort($seatIds);

        return Order::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', OrderStatus::Pending)
            ->where('expires_at', '>', now())
            ->get()
            ->first(function (Order $order) use ($seatIds): bool {
                $orderSeatIds = $order->items()->pluck('seat_id')->sort()->values()->toArray();

                return $orderSeatIds === $seatIds;
            });
    }
}
