<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomAvailabilityController extends Controller
{
    public function show(Request $request, $roomId){
        $start = Carbon::parse(
            $request->query('start', now()->startOfMonth())
        )->startOfDay();

        $end = Carbon::parse(
            $request->query('end', now()->endOfMonth())
        )->startOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            return response()->json([
                'message' => 'Invalid date range'
            ], 422);
        }

        $unavailableDates = [];

        // BOOKINGS
        $bookings = Booking::where('room_id', $roomId)
            ->whereIn('status', [
                'pending_payment',
                'waiting_confirmation',
                'paid'
            ])
            ->where(function ($q) use ($start, $end) {
                $q->where('check_in_date', '<', $end)
                ->where('check_out_date', '>', $start);
            })
            ->get(['check_in_date', 'check_out_date']);

        foreach ($bookings as $booking) {
            $periodStart = max(
                Carbon::parse($booking->check_in_date),
                $start
            );

            $periodEnd = min(
                Carbon::parse($booking->check_out_date),
                $end
            );

            foreach ($periodStart->daysUntil($periodEnd) as $date) {
                $unavailableDates[] = $date->toDateString();
            }
        }

        // ROOM BLOCKS
        $blocks = RoomBlock::where('room_id', $roomId)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<', $end)
                ->where('end_date', '>', $start);
            })
            ->get(['start_date', 'end_date']);

        foreach ($blocks as $block) {
            $periodStart = max(
                Carbon::parse($block->start_date),
                $start
            );

            $periodEnd = min(
                Carbon::parse($block->end_date),
                $end
            );

            foreach ($periodStart->daysUntil($periodEnd) as $date) {
                $unavailableDates[] = $date->toDateString();
            }
        }

        return response()->json([
            'room_id' => (int) $roomId,
            'unavailable_dates' => array_values(array_unique($unavailableDates)),
        ]);
    }

}
