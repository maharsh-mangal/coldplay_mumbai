<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function upcomingEvents(): HasMany
    {
        return $this->hasMany(Event::class)
            ->where('status', 'upcoming')
            ->orderBy('event_date');
    }
}
