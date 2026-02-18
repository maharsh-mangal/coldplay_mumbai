<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomElement([450000, 1200000, 2500000]);
        $tax = (int) round($subtotal * Order::GST_RATE);

        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'address_id' => Address::factory(),
            'status' => OrderStatus::Pending,
            'subtotal_in_paisa' => $subtotal,
            'tax_in_paisa' => $tax,
            'total_in_paisa' => $subtotal + $tax,
            'expires_at' => now()->addMinutes(10),
            'confirmed_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state([
            'status' => OrderStatus::Confirmed,
            'confirmed_at' => now(),
            'expires_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => OrderStatus::Cancelled,
            'expires_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => OrderStatus::Expired,
            'expires_at' => now()->subMinute(),
        ]);
    }
}
