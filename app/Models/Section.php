<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_in_paisa' => 'integer',
            'sort_order' => 'integer',
            'layout' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function availableSeats(): HasMany
    {
        return $this->hasMany(Seat::class)->where('status', 'available');
    }

    public function priceInRupees(): float
    {
        return $this->price_in_paisa / 100;
    }

    public function formattedPrice(): string
    {
        return '₹'.number_format($this->priceInRupees(), 2);
    }
}
