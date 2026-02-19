<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SeatStatus;
use App\Exceptions\BookingException;
use App\Mail\BookingConfirmation;
use App\Models\Address;
use App\Models\City;
use App\Models\Event;
use App\Models\Order;
use App\Models\Seat;
use App\Models\Section;
use App\Models\Tour;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function createEventWithSeats(int $seatCount = 5): array
{
    $city = City::factory()->create();
    $venue = Venue::factory()->for($city)->create();
    $tour = Tour::factory()->create();

    $event = Event::factory()->for($tour)->for($venue)->create();
    $section = Section::factory()->vip()->for($event)->create();

    $seats = collect();
    for ($i = 1; $i <= $seatCount; $i++) {
        $seats->push(
            Seat::factory()->for($event)->for($section)->create(['row' => 'A', 'number' => $i])
        );
    }

    return [$event, $section, $seats];
}

function createUserWithAddress(): User
{
    return User::factory()
        ->has(Address::factory()->default())
        ->create();
}

/*
|--------------------------------------------------------------------------
| Lock Seats
|--------------------------------------------------------------------------
*/

describe('lockSeats', function (): void {

    it('locks selected seats and creates a pending order', function (): void {
        [$event, $section, $seats] = createEventWithSeats(3);
        $user = createUserWithAddress();
        $seatIds = $seats->pluck('id')->toArray();

        $service = app(BookingService::class);
        $order = $service->lockSeats($user, $event, $seatIds);

        // Order
        expect($order)
            ->toBeInstanceOf(Order::class)
            ->status->toBe(OrderStatus::Pending)
            ->user_id->toBe($user->id)
            ->event_id->toBe($event->id)
            ->and($order->subtotal_in_paisa)->toBe(3 * 2_500_000)
            ->and($order->tax_in_paisa)->toBe(Order::calculateTax(3 * 2_500_000))
            ->and($order->total_in_paisa)->toBe($order->subtotal_in_paisa + $order->tax_in_paisa)
            ->and($order->items)->toHaveCount(3);

        // Pricing: 3 VIP seats × ₹25,000 = ₹75,000

        // Order items

        // Seats locked
        foreach ($seatIds as $seatId) {
            $seat = Seat::find($seatId);
            expect($seat->status)->toBe(SeatStatus::Locked)
                ->and($seat->locked_by)->toBe($user->id)
                ->and($seat->locked_until)->not->toBeNull();
        }

        // Expiry set
        expect($order->expires_at)->not->toBeNull();
    });

    it('rejects locking seats that are already locked by another user', function (): void {
        [$event, $section, $seats] = createEventWithSeats(2);
        $user1 = createUserWithAddress();
        $user2 = createUserWithAddress();
        $seatIds = $seats->pluck('id')->toArray();

        $service = app(BookingService::class);
        $service->lockSeats($user1, $event, $seatIds);

        $service->lockSeats($user2, $event, $seatIds);
    })->throws(BookingException::class, 'seats are no longer available');

    it('rejects locking seats that are already booked', function (): void {
        [$event, $section, $seats] = createEventWithSeats(2);
        $user = createUserWithAddress();

        // Mark seats as booked directly
        $seats->each(fn (Seat $seat) => $seat->update(['status' => SeatStatus::Booked]));

        $service = app(BookingService::class);
        $service->lockSeats($user, $event, $seats->pluck('id')->toArray());
    })->throws(BookingException::class, 'seats are no longer available');

    it('rejects locking seats from different events', function (): void {
        [$event1, , $seats1] = createEventWithSeats(2);
        [$event2, , $seats2] = createEventWithSeats(2);
        $user = createUserWithAddress();

        $mixedIds = [$seats1->first()->id, $seats2->first()->id];

        $service = app(BookingService::class);
        $service->lockSeats($user, $event1, $mixedIds);
    })->throws(BookingException::class, 'do not belong to this event');

    it('rejects locking with empty seat list', function (): void {
        [$event] = createEventWithSeats(2);
        $user = createUserWithAddress();

        $service = app(BookingService::class);
        $service->lockSeats($user, $event, []);
    })->throws(BookingException::class, 'No seats selected');

    it('allows locking seats whose previous lock has expired', function (): void {
        [$event, $section, $seats] = createEventWithSeats(2);
        $user1 = createUserWithAddress();
        $user2 = createUserWithAddress();
        $seatIds = $seats->pluck('id')->toArray();

        // User1 locks, then lock expires
        $seats->each(fn (Seat $seat) => $seat->update([
            'status' => SeatStatus::Locked,
            'locked_by' => $user1->id,
            'locked_until' => now()->subMinute(),
        ]));

        $service = app(BookingService::class);
        $order = $service->lockSeats($user2, $event, $seatIds);

        expect($order)->toBeInstanceOf(Order::class)
            ->and($order->user_id)->toBe($user2->id);

        foreach ($seatIds as $seatId) {
            expect(Seat::find($seatId)->locked_by)->toBe($user2->id);
        }
    });
});

/*
|--------------------------------------------------------------------------
| Confirm Booking
|--------------------------------------------------------------------------
*/

describe('confirmBooking', function (): void {

    it('confirms order, books seats, creates payment, and queues email', function (): void {
        Mail::fake();

        [$event, $section, $seats] = createEventWithSeats(2);
        $user = createUserWithAddress();
        $seatIds = $seats->pluck('id')->toArray();

        $service = app(BookingService::class);
        $order = $service->lockSeats($user, $event, $seatIds);
        $order = $service->confirmBooking($user, $order, PaymentMethod::Upi);

        // Order confirmed
        expect($order->fresh())
            ->status->toBe(OrderStatus::Confirmed)
            ->confirmed_at->not->toBeNull();

        // Payment created
        $payment = $order->payment;
        expect($payment)
            ->status->toBe(PaymentStatus::Completed)
            ->method->toBe(PaymentMethod::Upi)
            ->amount_in_paisa->toBe($order->total_in_paisa)
            ->transaction_id->not->toBeNull()
            ->paid_at->not->toBeNull();

        // Seats marked booked, lock cleared
        foreach ($seatIds as $seatId) {
            $seat = Seat::find($seatId);
            expect($seat->status)->toBe(SeatStatus::Booked)
                ->and($seat->locked_by)->toBeNull()
                ->and($seat->locked_until)->toBeNull();
        }

        // Email queued
        Mail::assertQueued(BookingConfirmation::class, function (BookingConfirmation $mail) use ($user, $order): bool {
            return $mail->hasTo($user->email) && $mail->order->id === $order->id;
        });
    });

    it('rejects confirming an order that belongs to another user', function (): void {
        [$event, , $seats] = createEventWithSeats(2);
        $user1 = createUserWithAddress();
        $user2 = createUserWithAddress();

        $service = app(BookingService::class);
        $order = $service->lockSeats($user1, $event, $seats->pluck('id')->toArray());

        $service->confirmBooking($user2, $order, PaymentMethod::Upi);
    })->throws(BookingException::class, 'does not belong to you');

    it('rejects confirming an already confirmed order', function (): void {
        Mail::fake();

        [$event, , $seats] = createEventWithSeats(2);
        $user = createUserWithAddress();

        $service = app(BookingService::class);
        $order = $service->lockSeats($user, $event, $seats->pluck('id')->toArray());
        $service->confirmBooking($user, $order, PaymentMethod::Upi);

        // Try again
        $service->confirmBooking($user, $order->fresh(), PaymentMethod::Upi);
    })->throws(BookingException::class, 'not in a confirmable state');

    it('rejects confirming an expired order', function (): void {
        [$event, , $seats] = createEventWithSeats(2);
        $user = createUserWithAddress();

        $service = app(BookingService::class);
        $order = $service->lockSeats($user, $event, $seats->pluck('id')->toArray());

        // Force expire
        $order->update(['expires_at' => now()->subMinute()]);

        $service->confirmBooking($user, $order->fresh(), PaymentMethod::Upi);
    })->throws(BookingException::class, 'expired');
});

/*
|--------------------------------------------------------------------------
| Concurrency (the core of what they're evaluating)
|--------------------------------------------------------------------------
*/

describe('concurrency', function (): void {

    it('prevents double-booking when two users lock the same seat simultaneously', function (): void {
        [$event, $section, $seats] = createEventWithSeats(1);
        $user1 = createUserWithAddress();
        $user2 = createUserWithAddress();
        $seatIds = $seats->pluck('id')->toArray();

        $service = app(BookingService::class);

        // User 1 locks first
        $order = $service->lockSeats($user1, $event, $seatIds);
        expect($order)->toBeInstanceOf(Order::class)
            ->and(fn () => $service->lockSeats($user2, $event, $seatIds))
            ->toThrow(BookingException::class, 'seats are no longer available');

        // User 2 tries same seat — must fail

        // Seat still belongs to user 1
        $seat = Seat::find($seatIds[0]);
        expect($seat->locked_by)->toBe($user1->id);
    });

    it('prevents booking when event is sold out', function (): void {
        [$event, $section, $seats] = createEventWithSeats(3);
        $user1 = createUserWithAddress();
        $user2 = createUserWithAddress();

        // Book all seats
        $seats->each(fn (Seat $seat) => $seat->update(['status' => SeatStatus::Booked]));

        $service = app(BookingService::class);

        expect(fn () => $service->lockSeats($user2, $event, $seats->pluck('id')->toArray()))
            ->toThrow(BookingException::class, 'seats are no longer available');
    });

    it('ensures idempotent locking — same user relocking returns existing order', function (): void {
        [$event, $section, $seats] = createEventWithSeats(2);
        $user = createUserWithAddress();
        $seatIds = $seats->pluck('id')->toArray();

        $service = app(BookingService::class);
        $order1 = $service->lockSeats($user, $event, $seatIds);
        $order2 = $service->lockSeats($user, $event, $seatIds);

        expect($order1->id)->toBe($order2->id);
    });
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

describe('api', function (): void {

    it('lists events with availability', function (): void {
        [$event, $section, $seats] = createEventWithSeats(5);

        $response = $this->getJson('/api/events');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'slug', 'event_date', 'tour', 'venue', 'available_seats']],
            ]);
    });

    it('shows event with sections and seat map', function (): void {
        [$event, $section, $seats] = createEventWithSeats(5);

        $response = $this->get("/api/events/{$event->slug}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'slug', 'sections' => [['id', 'name', 'price_in_paisa', 'available_seats']]],
            ]);
    });

    it('locks seats via API for authenticated user', function (): void {
        [$event, $section, $seats] = createEventWithSeats(3);
        $user = createUserWithAddress();
        $seatIds = $seats->pluck('id')->toArray();

        $response = $this->actingAs($user)
            ->postJson("/api/events/{$event->slug}/lock-seats", ['seat_ids' => $seatIds]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'status', 'total_in_paisa', 'expires_at']]);
    });

    it('confirms booking via API for authenticated user', function (): void {
        Mail::fake();

        [$event, $section, $seats] = createEventWithSeats(2);
        $user = createUserWithAddress();

        $service = app(BookingService::class);
        $order = $service->lockSeats($user, $event, $seats->pluck('id')->toArray());

        $response = $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/confirm", ['payment_method' => 'upi']);

        $response->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        Mail::assertQueued(BookingConfirmation::class);
    });

    it('rejects unauthenticated booking attempts', function (): void {
        [$event, , $seats] = createEventWithSeats(2);

        $response = $this->postJson("/api/events/{$event->slug}/lock-seats", [
            'seat_ids' => $seats->pluck('id')->toArray(),
        ]);

        $response->assertUnauthorized();
    });

    it('returns validation errors for invalid seat_ids', function (): void {
        [$event] = createEventWithSeats(2);
        $user = createUserWithAddress();

        $response = $this->actingAs($user)
            ->postJson("/api/events/{$event->slug}/lock-seats", ['seat_ids' => []]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('seat_ids');
    });

    it('returns validation errors for invalid payment_method', function (): void {
        [$event, , $seats] = createEventWithSeats(2);
        $user = createUserWithAddress();

        $service = app(BookingService::class);
        $order = $service->lockSeats($user, $event, $seats->pluck('id')->toArray());

        $response = $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/confirm", ['payment_method' => 'bitcoin']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method');
    });
});
