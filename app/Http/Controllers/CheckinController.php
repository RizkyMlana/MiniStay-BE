<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function checkin(Request $request){
        $request->validate([
            'booking_code' => 'required|string',
        ]);

        $booking = Booking::where('booking_code', $request->booking_code)
            ->firstOrFail();

        if($booking->status !== 'paid') {
            return response()->json([
                'message' => 'Booking not paid or already used'
            ], 422);
        }

        if(Carbon::today()->lt(Carbon::parse($booking->check_in_date))) {
            return response()->json([
                'message' => 'Check-in date not reached'
            ], 422);
        }

        $booking->update([
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'Check-in success, booking completed'
        ]);
    }
}
