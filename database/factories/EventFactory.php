<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Tour;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'venue_id' => Venue::factory(),
            'slug' => Str::slug(fake()->sentence(3)).'-'.fake()->unique()->randomNumber(5),
            'event_date' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'status' => EventStatus::Upcoming,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => EventStatus::Completed,
            'event_date' => fake()->dateTimeBetween('-6 months', '-1 week'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => EventStatus::Cancelled,
        ]);
    }
}
