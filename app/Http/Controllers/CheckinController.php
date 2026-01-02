<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function checkIn(string $token){
        $booking = Booking::where('qr_token', $token)->firstOrFail();

        if($booking->status !== 'paid') {
            abort(400, 'Booking not paid');
        }
        if($booking->checked_in_at) {
            abort(400, 'Already checked in');
        }
        if(Carbon::today()->lt($booking->check_in_date)) {
            abort(400, 'Check in date not reached');
        }

        $booking->update([
            'checked_in_at' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Checked in']);
    }
}
