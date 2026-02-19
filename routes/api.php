<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Support\Facades\Route;

// Public — browse events
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{slug}', [EventController::class, 'show']);

// Authenticated — booking flow
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/events/{slug}/lock-seats', [BookingController::class, 'lockSeats']);
    Route::post('/orders/{order}/confirm', [BookingController::class, 'confirm']);
});
