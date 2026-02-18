<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SeatStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'status' => SeatStatus::class,
            'locked_until' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * @param  Builder<Seat>  $query
     * @return Builder<Seat>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', SeatStatus::Available);
    }

    /**
     * Seats that are locked but the hold has expired.
     *
     * @param  Builder<Seat>  $query
     * @return Builder<Seat>
     */
    public function scopeExpiredLocks(Builder $query): Builder
    {
        return $query->where('status', SeatStatus::Locked)
            ->where('locked_until', '<', now());
    }

    public function isAvailable(): bool
    {
        return $this->status === SeatStatus::Available;
    }

    public function isLocked(): bool
    {
        return $this->status === SeatStatus::Locked;
    }

    public function isBooked(): bool
    {
        return $this->status === SeatStatus::Booked;
    }

    public function isLockExpired(): bool
    {
        return $this->isLocked() && $this->locked_until?->isPast();
    }

    public function isLockedBy(User $user): bool
    {
        return $this->isLocked() && $this->locked_by === $user->id;
    }

    public function label(): string
    {
        return "{$this->section->name} — Row {$this->row}, Seat {$this->number}";
    }
}
