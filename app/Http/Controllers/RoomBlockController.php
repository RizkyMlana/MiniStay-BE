<?php

namespace App\Http\Controllers;

use App\Models\RoomBlock;
use Illuminate\Http\Request;

class RoomBlockController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'room_id' => 'required|exists|rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string',
        ]);

        $block = RoomBlock::create($request->all());

        return response()->json($block, 201);
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
