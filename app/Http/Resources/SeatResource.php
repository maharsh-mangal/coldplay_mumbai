<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Seat
 */
class SeatResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'row' => $this->row,
            'number' => $this->number,
            'status' => $this->status->value,
        ];
    }
}
