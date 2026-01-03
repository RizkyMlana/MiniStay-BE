<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(){
        return Room::select(
            'id',
            'name',
            'price_per_day',
            'thumbnail',
        )
        ->where('is_active', true)->get();
    }
    public function show($id){
        return Room::with('images')->findOrFail($id);
    }

    public function availability(Request $request, $id){
        $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:date:check_in_date',
        ]);

        $conflict = Booking::where('room_id', $id)
        ->whereIn('status', ['pending','paid'])
        ->where(function ($q) use ($request) {
            $q->where('check_in_date', '<', $request->check_out_date)
            ->where('check_out_date', '>', $request->check_in_date);
        })
        ->exists();
        return ['available' => !$conflict];
    }
}
