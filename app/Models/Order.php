<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    /** GST rate for entertainment services */
    public const float GST_RATE = 0.18;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal_in_paisa' => 'integer',
            'tax_in_paisa' => 'integer',
            'total_in_paisa' => 'integer',
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending)
            ->where('expires_at', '<', now());
    }

    public function isExpired(): bool
    {
        return $this->status === OrderStatus::Pending
            && $this->expires_at?->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === OrderStatus::Confirmed;
    }

    public static function calculateTax(int $subtotalInPaisa): int
    {
        return (int) round($subtotalInPaisa * self::GST_RATE);
    }

    public function formattedTotal(): string
    {
        return '₹'.number_format($this->total_in_paisa / 100, 2);
    }
}
