<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use App\Models\Event;
use App\Models\Seat;
use App\Models\Section;
use App\Models\Tour;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::factory()->create([
            'name' => 'Mumbai',
            'state' => 'Maharashtra',
            'country' => 'India'
        ]);

        $venue = Venue::factory()->for($city)->create([
            'name' => 'DY Patil Stadium',
            'address' => 'Nerul, Navi Mumbai, Maharashtra 400706',
            'capacity' => 55000
        ]);

        $tour = Tour::factory()->create([
            'name' => 'Music of the Spheres World Tour',
            'slug' => 'coldplay-music-of-the-spheres-2025',
            'artist' => 'Coldplay',
            'description' => 'Coldplay returns to India.'
        ]);

        User::factory()
            ->state(['name' => 'Test User', 'email' => 'test@example.com', 'password' => Hash::make('password')])
            ->has(Address::factory()->default()->state(['label' => 'Home', 'city' => 'Mumbai', 'state' => 'Maharashtra']))
            ->has(Address::factory()->state(['label' => 'Office', 'city' => 'Mumbai', 'state' => 'Maharashtra']))
            ->create();

        User::factory()
            ->state(['name' => 'Concurrent User', 'email' => 'concurrent@example.com', 'password' => Hash::make('password')])
            ->has(Address::factory()->default()->state(['label' => 'Home', 'city' => 'Mumbai', 'state' => 'Maharashtra']))
            ->has(Address::factory()->state(['label' => 'Office', 'city' => 'Mumbai', 'state' => 'Maharashtra']))
            ->create();

        Event::factory()
            ->for($tour)
            ->for($venue)
            ->has(Section::factory()->vip(), 'sections')
            ->has(Section::factory()->premium(), 'sections')
            ->has(Section::factory()->general(), 'sections')
            ->create(['slug' => 'coldplay-mumbai-18-jan-2025', 'event_date' => '2025-01-18 19:00:00']);

        $this->seedSeats();
    }

    private function seedSeats(): void
    {
        $rowLetters = range('A', 'Z');

        Section::all()->each(function (Section $section) use ($rowLetters): void {
            $seats = [];
            $now = now();

            for ($r = 0; $r < $section->layout['row_count']; $r++) {
                for ($s = 1; $s <= $section->layout['seats_per_row']; $s++) {
                    $seats[] = [
                        'event_id' => $section->event_id,
                        'section_id' => $section->id,
                        'row' => $rowLetters[$r],
                        'number' => $s,
                        'status' => 'available',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            Seat::insert($seats);
        });
    }
}
