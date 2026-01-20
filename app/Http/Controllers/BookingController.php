<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $user = $request->user();

        $checkIn  = Carbon::parse($request->check_in_date)->startOfDay();
        $checkOut = Carbon::parse($request->check_out_date)->startOfDay();

        $conflictBooking = Booking::where('room_id', $request->room_id)
            ->whereIn('status', [
                'pending_payment',
                'waiting_confirmation',
                'paid'
            ])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in_date', '<', $checkOut)
                  ->where('check_out_date', '>', $checkIn);
            })
            ->exists();

        if ($conflictBooking) {
            return response()->json([
                'message' => 'Room not available'
            ], 422);
        }

        $conflictBlock = RoomBlock::where('room_id', $request->room_id)
            ->where('start_date', '<', $checkOut)
            ->where('end_date', '>', $checkIn)
            ->exists();

        if ($conflictBlock) {
            return response()->json([
                'message' => 'Room is blocked'
            ], 422);
        }

        $room = Room::findOrFail($request->room_id);
        $days = $checkIn->diffInDays($checkOut);

        if ($days < 1) {
            return response()->json([
                'message' => 'Minimum 1 night'
            ], 422);
        }

        $totalPrice = $days * $room->price_per_day;

        $booking = DB::transaction(function () use (
            $user,
            $room,
            $checkIn,
            $checkOut,
            $totalPrice
        ) {
            $booking = Booking::create([
                'booking_code'     => 'MS-' . strtoupper(Str::random(8)),
                'user_id'          => $user->id,
                'room_id'          => $room->id,
                'check_in_date'    => $checkIn,
                'check_out_date'   => $checkOut,
                'status'           => 'pending_payment',
                'total_price'      => $totalPrice,
                'payment_deadline' => now()->addHours(6),
            ]);

            RoomBlock::create([
                'room_id'    => $room->id,
                'start_date' => $checkIn,
                'end_date'   => $checkOut,
                'reason'     => 'booking',
            ]);

            return $booking;
        });

        return response()->json($booking, 201);
    }



    public function myBookings(Request $request){
        return Booking::where('user_id', $request->user()->id)
            ->with('room')
            ->orderByDesc('created_at')
            ->get();
    }


    public function index()
    {
        return Booking::with(['user', 'room', 'payment'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:completed'
        ]);

        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'paid') {
            return response()->json([
                'message' => 'Only paid bookings can be completed'
            ], 422);
        }

        $booking->update([
            'status' => 'completed'
        ]);

        return response()->json([
            'message' => 'Booking marked as completed',
            'booking' => $booking
        ]);
    }


}
