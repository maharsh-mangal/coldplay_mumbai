<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Section
 */
class SectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price_in_paisa' => $this->price_in_paisa,
            'formatted_price' => $this->formattedPrice(),
            'layout' => $this->layout,
            'seats' => $this->relationLoaded('seats')
                ? SeatResource::collection($this->seats)->resolve()
                : [],
            'available_seats' => $this->availableSeats()->count(),
        ];
    }
}
