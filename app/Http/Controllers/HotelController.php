<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomAvailable;

class HotelController extends Controller
{
    public function index()
    {
        $rooms = Room::select('id', 'name', 'price_per_day', 'facilities')
                ->withCount(['availabilities as available_days' => function ($q) {
                        $q->where('status', 'available'); 
                    }
                ])
                ->with('firstPhoto:id,room_id,url')
                ->get();

        return response()->json([
            'message' => 'List rooms',
            'data' => $rooms
        ]);
    }

    public function show($id){
        $room = Room::with([
            'photos',
            'availabilities' => function($q) {
                $q->orderBy('date', 'asc');
            }
        ])->find($id);

        if(!$room) {
            return response()->json(['message' => 'Room Not Found'], 404);
        }
        return response()->json([
            'message'=>'Room detail',
            'data'=> $room
        ]);
    }   
    public function checkAvailability(Request $request){
        $request->validate([
            'room_id'=>'required|integer',
            'date'=> 'required|date'
        ]);
        $available = RoomAvailable::where('room_id', $request->room_id)
            ->where('date', $request->date)
            ->where('status', 'available')
            ->exist();
        return response()->json([
            'room_id'=>$request->room_id,
            'date'=>$request->date,
            'available'=>$available
        ]);
    }

    public function searchAvailable(Request $request){
        $request->validate([
            'check_in'=> 'required|date',
            'check_out'=> 'required|date|after:check_in'
        ]);

        $dates = [];
        $start = strtotime($request->check_in);
        $end = strtotime($request->check_out);

        for ($d = $start; $d <= $end; $d += 86400){
            $dates[] = date('Y-m-d', $d);
        }

        $room = Room::whereDoesntHave('availability', function($q) use ($dates){
            $q->whereIn('date', $dates)
                ->where('status', 'booked');
        })->get();

        return response()->json([
            'message' => 'Available rooms',
            'dates' => $dates,
            'data' => $room
        ]);
    }

}
