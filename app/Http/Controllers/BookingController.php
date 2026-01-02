<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request, BookingService $service){
        $request->validate([
            'room_id'=>'required|exists:rooms,id',
            'check_in_date'=>'required|date',
            'check_out_date'=>'required|date|after:check_in_date',
        ]);

        $booking = $service->create(
            auth()->id(),
            $request->room_id,
            $request->check_in_date,
            $request->check_out_date,
        );

        return response()->json([
            'message' => 'Booking berhasil dibuat',
            'booking' => $booking,
        ], 201);
    }
}
