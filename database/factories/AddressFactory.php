<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Office', 'Other']),
            'line_1' => fake()->streetAddress(),
            'line_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->word(),
            'postal_code' => fake()->numerify('######'),
            'phone' => fake()->numerify('98########'),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state([
            'is_default' => true,
        ]);
    }
}
