<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'subtotal_in_paisa' => $this->subtotal_in_paisa,
            'tax_in_paisa' => $this->tax_in_paisa,
            'total_in_paisa' => $this->total_in_paisa,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'event' => $this->when($this->relationLoaded('event'), fn () => [
                'slug' => $this->event->slug,
                'event_date' => $this->event->event_date->toIso8601String(),
                'tour' => $this->when($this->event->relationLoaded('tour'), fn () => [
                    'name' => $this->event->tour->name,
                    'artist' => $this->event->tour->artist,
                ]),
                'venue' => $this->when($this->event->relationLoaded('venue'), fn () => [
                    'name' => $this->event->venue->name,
                    'city' => $this->event->venue->city->name,
                ]),
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => $this->when($this->relationLoaded('payment') && $this->payment, fn () => [
                'method' => $this->payment->method->value,
                'transaction_id' => $this->payment->transaction_id,
                'paid_at' => $this->payment->paid_at?->toIso8601String(),
            ]),
        ];
    }
}
