<?php

namespace App\Jobs;

use App\Models\Booking;
use Carbon\Carbon;

class ExpirePendingBookings{
    public function handle(): void
    {
        Booking::where('status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subMinutes(30))
            ->chunkById(50, function ($bookings) {
                foreach ($bookings as $booking) {
                    $booking->update([
                        'status' => 'cancelled',
                    ]);

                    // release room block
                    $booking->roomBlocks()->delete();
                }
            });
    }
}
