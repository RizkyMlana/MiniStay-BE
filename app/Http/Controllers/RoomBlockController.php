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
            $start = \Carbon\Carbon::parse($block->start_date);
            $end = \Carbon\Carbon::parse($block->end_date);

            // loop tiap tanggal dari start sampai end
            for ($date = $start; $date->lte($end); $date->addDay()) {
                if ($date->lt($today)) {
                    $status = 'kadaluarsa';
                } else {
                    $status = 'terisi';
                }

                $calendarData[] = [
                    'date' => $date->format('Y-m-d'),
                    'status' => $status
                ];
            }
        }

        return response()->json($calendarData);
    }

}
