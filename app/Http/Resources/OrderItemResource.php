<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seat_id' => $this->seat_id,
            'price_in_paisa' => $this->price_in_paisa,
            'seat_label' => $this->when(
                $this->relationLoaded('seat') && $this->seat->relationLoaded('section'),
                fn () => "{$this->seat->section->name} — Row {$this->seat->row}, Seat {$this->seat->number}",
            ),
        ];
    }
}
