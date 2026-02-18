<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SeatStatus;
use App\Models\Event;
use App\Models\Seat;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seat>
 */
class SeatFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'section_id' => Section::factory(),
            'row' => fake()->randomElement(['A', 'B', 'C', 'D', 'E']),
            'number' => fake()->numberBetween(1, 50),
            'status' => SeatStatus::Available,
            'locked_by' => null,
            'locked_until' => null,
        ];
    }

    public function locked(int $userId): static
    {
        return $this->state([
            'status' => SeatStatus::Locked,
            'locked_by' => $userId,
            'locked_until' => now()->addMinutes(10),
        ]);
    }

    public function expiredLock(int $userId): static
    {
        return $this->state([
            'status' => SeatStatus::Locked,
            'locked_by' => $userId,
            'locked_until' => now()->subMinute(),
        ]);
    }

    public function booked(): static
    {
        return $this->state([
            'status' => SeatStatus::Booked,
            'locked_by' => null,
            'locked_until' => null,
        ]);
    }
}
