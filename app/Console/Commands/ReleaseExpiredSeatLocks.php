<?php
namespace App\Console\Commands;

use App\Enums\SeatStatus;
use App\Models\Seat;
use Illuminate\Console\Command;

class ReleaseExpiredSeatLocks extends Command
{
    protected $signature = 'seats:release-expired';
    protected $description = 'Release seat locks that have expired';

    public function handle(): int
    {
        $released = Seat::query()
            ->where('status', SeatStatus::Locked)
            ->where('locked_until', '<', now())
            ->update([
                'status' => SeatStatus::Available,
                'locked_until' => null,
                'locked_by_user_id' => null,
            ]);

        $this->info("Released {$released} expired seat locks.");

        return 0;
    }
}
