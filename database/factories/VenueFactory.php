<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name' => fake()->company().' Stadium',
            'address' => fake()->address(),
            'capacity' => fake()->numberBetween(5000, 100000),
            'map_url' => null,
        ];
    }
}
