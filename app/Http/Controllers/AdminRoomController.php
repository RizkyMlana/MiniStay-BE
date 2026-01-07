<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class AdminRoomController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price_per_day' => 'required|numeric',
            'capacity' => 'required|integer',
        ]);

        $room = Room::create($request->all());

        return response()->json($room, 201);
    }

    public function update(Request $request, $id){
        $room = Room::findOrFail($id);
        $room->update($request->only([
            'name',
            'description',
            'price_per_day',
            'capacity'
        ]));

        return response()->json($room);
    }

    public function destroy($id){
        Room::findOrFail($id)->delete();
        return response()->json(['message' => 'Room Deleted']);
    }
}
