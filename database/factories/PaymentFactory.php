<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount_in_paisa' => fake()->randomElement([450000, 1200000, 2500000]),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'status' => PaymentStatus::Pending,
            'transaction_id' => null,
            'paid_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => PaymentStatus::Completed,
            'transaction_id' => 'TXN_'.Str::upper(Str::random(12)),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => PaymentStatus::Failed,
            'transaction_id' => 'TXN_'.Str::upper(Str::random(12)),
        ]);
    }
}
