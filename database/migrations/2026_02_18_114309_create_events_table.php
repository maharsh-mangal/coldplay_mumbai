<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->dateTime('event_date');
            $table->string('status')->default('upcoming');
            $table->timestamps();

            $table->index('event_date');
            $table->index(['tour_id', 'venue_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
