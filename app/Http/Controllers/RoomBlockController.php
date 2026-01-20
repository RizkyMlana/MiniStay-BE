<?php

namespace App\Http\Controllers;

use App\Models\RoomBlock;
use App\Models\Booking;

use Illuminate\Http\Request;

class RoomBlockController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date', // bisa block 1 hari
            'reason' => 'nullable|string',
        ]);

        $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();

        // 1. Cek overlap dengan block yang sudah ada
        $overlappingBlock = RoomBlock::where('room_id', $request->room_id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                });
            })
            ->first();

        if ($overlappingBlock) {
            return response()->json([
                'message' => 'Tanggal ini sudah diblokir sebelumnya'
            ], 422);
        }

        // 2. Cek overlap dengan booking aktif
        $activeStatuses = ['pending_payment', 'waiting_confirmation', 'paid'];
        $overlappingBooking = Booking::where('room_id', $request->room_id)
            ->whereIn('status', $activeStatuses)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('check_in_date', [$start, $end])
                ->orWhereBetween('check_out_date', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('check_in_date', '<=', $start)
                        ->where('check_out_date', '>=', $end);
                });
            })
            ->first();

        if ($overlappingBooking) {
            return response()->json([
                'message' => 'Tanggal ini sudah ada booking aktif'
            ], 422);
        }

        // 3. Simpan block
        $block = RoomBlock::create([
            'room_id' => $request->room_id,
            'start_date' => $start,
            'end_date' => $end,
            'reason' => $request->reason,
        ]);

        return response()->json($block, 201);
    }


    public function destroy($id){
        $block = RoomBlock::findOrFail($id);
        $block->delete();

        return response()->json([
            'message' => 'Block deleted'
        ], 200);
    }
    public function getRoomBlocks($roomId)
    {
        $blocks = RoomBlock::where('room_id', $roomId)->get();
        $today = now()->startOfDay();
        $calendarData = [];

        foreach ($blocks as $block) {
            $period = \Carbon\CarbonPeriod::create($block->start_date, $block->end_date);
            foreach ($period as $date) {
                $calendarData[$date->format('Y-m-d')] = $date->lt($today) ? 'kadaluarsa' : 'terisi';
            }
        }

        // convert associative array ke array untuk json
        $calendarData = collect($calendarData)->map(fn($status, $date) => ['date'=>$date,'status'=>$status])->values();

        return response()->json($calendarData);
    }

}
