<?php

namespace App\Jobs;

use App\Models\Booking;
use Carbon\Carbon;

class CompleteFinishedBookings
{
    public function handle(): void
    {
        Booking::where('status', 'paid')
            ->whereDate('check_out_date', '<', Carbon::today())
            ->update([
                'status' => 'completed',
            ]);
    }
}
