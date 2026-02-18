<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('address');
            $table->unsignedInteger('capacity');
            $table->string('map_url')->nullable();
            $table->timestamps();

            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
