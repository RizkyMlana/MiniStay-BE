<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class CheckinService
{
    public function checkin(string $token): Booking
    {
        $booking = Booking::where('qr_token', $token)->firstOrFail();

        if ($booking->status !== 'paid') {
            throw new \Exception('Booking not paid');
        }

        if ($booking->checked_in_at) {
            throw new \Exception('Already checked in');
        }

        if (Carbon::today()->lt($booking->check_in_date)) {
            throw new \Exception('Check-in date not reached');
        }

        $booking->update([
            'checked_in_at' => Carbon::now(),
        ]);

        return $booking;
    }
}