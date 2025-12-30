<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingScan;
use Illuminate\Http\Request;

class BookingScanController extends Controller
{

    public function scanQr(Request $request){
        $request->validate(['booking_code' => 'required']);

        $booking = Booking::where('booking_code', $request->booking_code)->firstOrFail();

        BookingScan::create([
            'booking_id' => $booking->id,
            'admin_id' => auth()->guard('admin')->id(),
        ]);

        $booking->status = 'checked_in';
        $booking->save();

        return response()->json(['message' => 'QR Valid,  check in success']);
    }



    public function listScans(){
        $data = BookingScan::with(['booking', 'admin'])->latest()->get();
        return response()->json($data);
    }
}
