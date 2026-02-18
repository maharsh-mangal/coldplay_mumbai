<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'status' => EventStatus::class,
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Upcoming)
            ->where('event_date', '>', now())
            ->orderBy('event_date');
    }

    public function availableSeatsCount(): int
    {
        return $this->seats()->where('status', 'available')->count();
    }

    public function totalSeatsCount(): int
    {
        return $this->seats()->count();
    }

    public function isSoldOut(): bool
    {
        return $this->availableSeatsCount() === 0;
    }
}
