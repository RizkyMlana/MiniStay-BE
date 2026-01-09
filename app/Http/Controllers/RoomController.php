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

    private function getNextAvailableDate(int $roomId): ?string{
        $activeBlock = RoomBlock::where('room_id', $roomId)
            ->where('end_date', '>=', today())
            ->orderBy('end_date', 'asc')
            ->first();

        return $activeBlock
            ? $activeBlock->end_date->addDay()->toDateString()
            : today()->toDateString();
    }
    public function index() {
        $room = Room::with(['images'])->get();

        $room->transform(function ($room) {
            $room->is_available = $this->isRoomAvailableToday($room->id);
            $room->next_available_date = $this->getNextAvailableDate($room->id);
            return $room;
        });
            
        return response()->json($room);
    }

    public function show($id){
        $room = Room::with('images')->findOrFail($id);

        return response()->json([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'price_per_day' => $room->price_per_day,
            'capacity' => $room->capacity,
            'images' => $room->images,
        ]);
    }
}
