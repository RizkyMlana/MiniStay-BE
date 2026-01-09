<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomAvailabilityController extends Controller
{
    public function show(Request $request, $roomId){
        $start = Carbon::parse($request->query('start', now()->startOfMonth()));
        $end = Carbon::parse($request->query('end', now()->endOfMonth()));

        $bookings = Booking::where('room_id', $roomId)
            ->whereIn('status', ['pending_payment', 'waiting_confirmation', 'paid'])
            ->get(['check_in_date', 'check_out_date']);

        
        $blocks = RoomBlock::where('room_id', $roomId)
            ->get(['start_date', 'end_date']);

        $unavailableDates = [];

        foreach($bookings as $booking) {
            $period = Carbon::parse($booking->check_in_date)
                ->daysUntil($booking->check_out_date);
            foreach ($period as $date) {
                $unavailableDates[] = $date->toDateString();
            }
        }
        
        foreach($blocks as $block) {
            $period = Carbon::parse($block->start_date)
                ->daysUntil($block->end_date);
            
            foreach($period as $date) {
                $unavailableDates[] = $date->toDateString();
            } 
        }

        return response()->json([
            'room_id' => $roomId,
            'unavailable_dates' => array_values(array_unique($unavailableDates)),
        ]);
    }
}
