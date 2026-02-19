<?php

declare(strict_types=1);

use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\EventController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Route::get('/', function () {
//    return Inertia::render('Welcome', [
//        'canRegister' => Features::enabled(Features::registration()),
//    ]);
// })->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public
Route::get('/', [EventController::class, 'index'])->name('home');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');

// Authenticated — booking flow
Route::middleware('auth')->group(function (): void {
    Route::post('/seats/available', [EventController::class, 'seatsAvailable']);
    Route::post('/events/{slug}/lock-seats', [BookingController::class, 'lockSeats'])->name('events.lock-seats');
    Route::get('/checkout/{order}', [BookingController::class, 'checkout'])->name('checkout');
    Route::post('/orders/{order}/confirm', [BookingController::class, 'confirm'])->name('orders.confirm');
    Route::get('/orders/{order}/confirmation', [BookingController::class, 'confirmation'])->name('confirmation');
});

require __DIR__.'/settings.php';
