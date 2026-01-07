<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomBlock;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private function isRoomAvailableToday(int $roomId): bool{
        return !RoomBlock::where('room_id', $roomId)
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->exists();
    }
    public function index() {
        $room = Room::with(['images'])
            ->get()
            ->map(function ($room) {
                $room->is_available = $this->isRoomAvailableToday($room->id);
                return $room;
            });
        return response()->json($room);
    }

    public function show($id){
        $room = Room::with(['images'])->findOrFail($id);

        return response()->json($room);
    }
}
