<?php

namespace App\Jobs;

use App\Models\Booking;
use Carbon\Carbon;

class SendCheckinReminder
{
    public function handle(): void
    {
        Booking::where('status', 'paid')
            ->whereDate('check_in_date', Carbon::tomorrow())
            ->each(function ($booking) {
                // panggil NotificationService
                // NotificationService::sendCheckinReminder($booking);
            });
    }
}
