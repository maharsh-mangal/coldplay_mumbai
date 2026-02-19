<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\SeatStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventDetailResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Seat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $events = Event::with(['tour', 'venue.city'])
            ->upcoming()
            ->get();

        return Inertia::render('Events/Index', [
            'events' => EventResource::collection($events)->resolve(),
        ]);
    }

    public function show(string $slug): Response
    {
        $event = Event::where('slug', $slug)
            ->with(['tour', 'venue.city', 'sections.seats'])
            ->firstOrFail();

        return Inertia::render('Events/Show', [
            'event' => new EventDetailResource($event)->resolve(),
        ]);
    }

    public function seatsAvailable(Request $request)
    {
        $seats = $request->input('seats', []);

        if (empty($seats)) {
            return response()->json(['available' => false, 'message' => 'No seats selected']);
        }

        $uniqueSeats = array_unique($seats);

        $availableCount = Seat::query()
            ->whereIn('id', $uniqueSeats)
            ->where('status', SeatStatus::Available)
            ->count();

        $isAvailable = $availableCount === count($uniqueSeats);

        return response()->json([
            'available' => $isAvailable,
            'message' => $isAvailable ? 'Seats available' : 'Some seats are no longer available',
        ]);
    }
}
