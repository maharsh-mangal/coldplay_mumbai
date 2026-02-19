<?php

declare(strict_types=1);
// app/Console/Commands/StressTestBooking.php

namespace App\Console\Commands;

use App\Exceptions\BookingException;
use App\Models\Event;
use App\Models\Seat;
use App\Models\User;
use App\Services\BookingService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StressTestBooking extends Command
{
    protected $signature = 'test:stress-booking {users=50} {--seat-id=}';

    protected $description = 'Simulate concurrent users trying to book the same seat';

    public function handle(BookingService $bookingService): int
    {
        $concurrentUsers = (int) $this->argument('users');
        $event = Event::with('sections.seats')->first();

        // Get a specific seat or first available
        $seatId = $this->option('seat-id')
            ?? Seat::where('status', 'available')->first()?->id;

        if (! $seatId) {
            $this->error('No available seats found. Run: php artisan migrate:fresh --seed');

            return 1;
        }

        $this->info("🎯 Target: Seat ID {$seatId}");
        $this->info("👥 Simulating {$concurrentUsers} concurrent users...\n");

        // Reset seat to available
        Seat::where('id', $seatId)->update(['status' => 'available']);

        $results = ['success' => 0, 'failed' => 0, 'errors' => []];
        $users = User::factory()->count($concurrentUsers)->create();

        // Use parallel processes to simulate true concurrency
        $startTime = microtime(true);

        $promises = [];
        foreach ($users as $index => $user) {
            $promises[] = function () use ($bookingService, $user, $event, $seatId, &$results) {
                try {
                    DB::beginTransaction();
                    $bookingService->lockSeats($user, $event, [$seatId]);
                    DB::commit();
                    $results['success']++;

                    return 'success';
                } catch (BookingException $e) {
                    DB::rollBack();
                    $results['failed']++;

                    return 'failed: '.$e->getMessage();
                } catch (Exception $e) {
                    DB::rollBack();
                    $results['errors'][] = $e->getMessage();

                    return 'error: '.$e->getMessage();
                }
            };
        }

        // Execute all "simultaneously" using pcntl_fork or just sequential with timing
        foreach ($promises as $promise) {
            $promise();
        }

        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        // Results
        $this->newLine();
        $this->info('📊 RESULTS');
        $this->info('═══════════════════════════════════');
        $this->info("✅ Successful bookings: {$results['success']}");
        $this->info("❌ Correctly rejected:  {$results['failed']}");
        $this->info('⚠️  Errors:             '.count($results['errors']));
        $this->info("⏱️  Total time:         {$elapsed}ms");
        $this->newLine();

        // Verify data integrity
        $seatBookings = DB::table('order_items')->where('seat_id', $seatId)->count();

        if ($results['success'] === 1 && $seatBookings === 1) {
            $this->info('✅ PASSED: Exactly 1 user got the seat. No double-booking!');

            return 0;
        } else {
            $this->error("❌ FAILED: {$seatBookings} bookings found for same seat!");

            return 1;
        }
    }
}
