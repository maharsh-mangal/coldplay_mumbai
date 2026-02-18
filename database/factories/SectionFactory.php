<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement(['VIP', 'Premium', 'General']),
            'price_in_paisa' => fake()->randomElement([2500000, 1200000, 450000]),
            'sort_order' => fake()->numberBetween(1, 3),
            'layout' => [
                'row_count' => 5,
                'seats_per_row' => 10,
                'color' => fake()->hexColor(),
                'position' => 'middle',
            ],
        ];
    }

    public function vip(): static
    {
        return $this->state([
            'name' => 'VIP',
            'price_in_paisa' => 2500000,
            'sort_order' => 1,
            'layout' => ['row_count' => 3, 'seats_per_row' => 10, 'color' => '#FFD700', 'position' => 'front'],
        ]);
    }

    public function premium(): static
    {
        return $this->state([
            'name' => 'Premium',
            'price_in_paisa' => 1200000,
            'sort_order' => 2,
            'layout' => ['row_count' => 5, 'seats_per_row' => 15, 'color' => '#C0C0C0', 'position' => 'middle'],
        ]);
    }

    public function general(): static
    {
        return $this->state([
            'name' => 'General',
            'price_in_paisa' => 450000,
            'sort_order' => 3,
            'layout' => ['row_count' => 8, 'seats_per_row' => 20, 'color' => '#4A90D9', 'position' => 'back'],
        ]);
    }
}
