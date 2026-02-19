<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
class EventDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'event_date' => $this->event_date->toIso8601String(),
            'status' => $this->status->value,
            'available_seats' => $this->availableSeatsCount(),
            'total_seats' => $this->totalSeatsCount(),
            'tour' => $this->when($this->relationLoaded('tour'), fn () => [
                'name' => $this->tour->name,
                'artist' => $this->tour->artist,
                'slug' => $this->tour->slug,
                'poster_url' => $this->tour->poster_url,
                'description' => $this->tour->description,
            ]),
            'venue' => $this->when($this->relationLoaded('venue'), fn () => [
                'name' => $this->venue->name,
                'city' => $this->venue->city->name,
                'address' => $this->venue->address,
                'map_url' => $this->venue->map_url,
            ]),
            'sections' => SectionResource::collection($this->whenLoaded('sections'))->resolve(),
        ];
    }
}
