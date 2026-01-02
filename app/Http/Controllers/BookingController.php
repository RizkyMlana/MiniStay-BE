<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBlock;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request){
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
        ]);

        $checkIn = Carbon::parse($data['check_in_date'])->startOfDay();
        $checkOut = Carbon::parse($data['check_out_date'])->startOfDay();

        if($checkOut <= $checkIn) {
            abort(422, 'Invalid date range');
        }

        $conflict = Booking::where('room_id', $data['room_id'])
            ->whereIn('status', ['pending', 'paid'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn);
            })
            ->exists();

        if($conflict){
            abort(409, 'Room not available');
        }

        $room = Room::findOrFail($data['room_id']);
        $days = $checkIn->diffInDays($checkOut);
        $totalPrice = $days * $room->price_per_day;

        $booking = DB::transaction(function () use ($room, $checkIn, $checkOut, $totalPrice) {
            $booking = Booking::create([
                'booking_code' => 'MS-' . strtoupper(uniqid()),
                'user_id' => auth()->id(),
                'room_id' => $room->id,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'status' => 'pending',
                'total_price' => $totalPrice,
            ]);

            RoomBlock::create([
                'room_id' => $room->id,
                'booking_id' => $booking->id,
                'start_date' => $checkIn,
                'end_date' => $checkOut,
                'type' => 'booking',
            ]);

            return $booking;
        });

        WhatsApp::send(
            auth()->user()->phone,
            "Booking {$booking->booking_code} berhasil dibuat. \nTotal: Rp{$booking->total_price}"
        );
        return response()->json($booking);

    }
}
