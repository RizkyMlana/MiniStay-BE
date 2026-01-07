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
}
