<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomAvailable;
use App\Models\RoomPhoto;
use Illuminate\Http\Request;
use App\Services\SupabaseService;

class RoomController extends Controller
{
    public function createRoom(Request $request){
        $data = $request->validate([
            'name' => 'required',
            'price_per_day' => 'required|integer',
            'description' => 'nullable',
            'facilities' => 'nullable|array'
        ]);

        $room = Room::create($data);
        return response()->json($room);
    }

    public function updateRoom(Request $request, $id){
        $room = Room::findOrFail($id);
        $room->update($request->all());
        return response()->json($room);
    }

    public function deleteRoom($id){
        Room::findOrFail($id)->delete();
        return response()->json(['message' => 'Room deleted']);
    }


    public function uploadPhoto(Request $request, $id){
        $request->validate([
            'photo' => 'required|file|image',
        ]);

        $file = $request->file('photo');
        $filename = "rooms/$id" . uniqid() . "." . $file->getClientOriginalExtension();

        $url = SupabaseService::upload($file, $filename);

        RoomPhoto::create([
            'room_id' => $id,
            'url' => $url,
            'is_360' => $request->is_360 ?? false
        ]);

        return response()->json(['message' => 'Photo uploaded', 'url' => $url]);
    }


    public function updateAvailability(Request $request, $id){
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:available, booked'
        ]);

        RoomAvailable::updateOrCreate(
            ['room_id' => $id, 'date' => $request->date],
            ['status' => $request->status]
        );

        return response()->json(['message' => 'Availability updated']);
    }

    public function calendarAdmin($id){
        $calendar = RoomAvailable::where('room_id', $id)
            ->orderBy('date')
            ->get();
        
            return response()->json($calendar);
    }
    
}
