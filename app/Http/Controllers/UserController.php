<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class UserController extends Controller
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
}
