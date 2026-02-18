<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'seat_id' => Seat::factory(),
            'price_in_paisa' => fake()->randomElement([450000, 1200000, 2500000]),
        ];
    }
}
