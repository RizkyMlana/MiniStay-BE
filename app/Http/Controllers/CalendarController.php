<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\RoomBlock;


class CalendarController extends Controller
{
    public function calendar(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'month'   => 'required|date_format:Y-m',
        ]);

        $roomId = $request->room_id;
        $start  = Carbon::parse($request->month)->startOfMonth();
        $end    = Carbon::parse($request->month)->endOfMonth();

        // 1. BOOKINGS (read-only)
        $bookings = Booking::where('room_id', $roomId)
            ->whereIn('status', ['pending_payment', 'paid'])
            ->where('check_in_date', '<=', $end)
            ->where('check_out_date', '>=', $start)
            ->get()
            ->map(function ($b) {
                return [
                    'type'  => 'booking',
                    'start' => $b->check_in_date->toDateString(),
                    'end'   => $b->check_out_date->toDateString(),
                ];
            });
        $blocks = RoomBlock::where('room_id', $roomId)
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->get()
            ->map(function ($b) {
                return [
                    'id'    => $b->id,
                    'type'  => 'block',
                    'start' => $b->start_date->toDateString(),
                    'end'   => $b->end_date->toDateString(),
                    'reason'=> $b->reason,
                ];
            });

        return response()->json([
            'calendar' => $bookings->merge($blocks),
        ]);
    }

    public function storeBlock(Request $request)
    {
        $request->validate([
            'room_id'    => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'reason'     => 'nullable|string',
        ]);

        $block = RoomBlock::create([
            'room_id'    => $request->room_id,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'reason'     => $request->reason ?? 'Admin block',
        ]);

        return response()->json($block, 201);
    }

    public function deleteBlock($id)
    {
        $block = RoomBlock::findOrFail($id);

        $block->delete();

        return response()->json([
            'message' => 'Block removed',
        ]);
    }
}
