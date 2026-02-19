<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\SeatStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventDetailResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $events = Event::query()
            ->with(['tour', 'venue.city'])
            ->upcoming()
            ->get();

        return EventResource::collection($events);
    }

    public function show(string $slug): EventDetailResource
    {
        $event = Event::query()->where('slug', $slug)
            ->with(['tour', 'venue.city', 'sections'])
            ->firstOrFail();

        return new EventDetailResource($event);
    }
}
