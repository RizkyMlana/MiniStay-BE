<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date_format:Y-m-d',
            'check_out_date' => 'required|date_format:Y-m-d|after:check_in_date',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $checkIn  = Carbon::parse($request->check_in_date)->startOfDay();
        $checkOut = Carbon::parse($request->check_out_date)->startOfDay();

        $room = Room::findOrFail($request->room_id);
        $days = $checkIn->diffInDays($checkOut);

        if ($days < 1) {
            return response()->json(['message' => 'Minimum 1 night'], 422);
        }

        $totalPrice = $days * $room->price_per_day;

        try {
            $booking = DB::transaction(function () use ($user, $room, $checkIn, $checkOut, $totalPrice) {

                $conflictBooking = Booking::where('room_id', $room->id)
                    ->whereIn('status', ['pending_payment','waiting_confirmation','paid'])
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in_date', '<', $checkOut)
                        ->where('check_out_date', '>', $checkIn);
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($conflictBooking) {
                    throw new \Exception('Room not available');
                }

                $conflictBlock = RoomBlock::where('room_id', $room->id)
                    ->where('start_date', '<', $checkOut)
                    ->where('end_date', '>', $checkIn)
                    ->lockForUpdate()
                    ->exists();

                if ($conflictBlock) {
                    throw new \Exception('Room is blocked');
                }
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


                return $booking;
            });
            try {
                WhatsApp::sendBookingConfirmation(
                    $user->phone,
                    $booking->booking_code,
                    $room->name,
                    $totalPrice,
                    $booking->payment_deadline
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send WA booking confirmation', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json($booking, 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }




    public function myBookings(Request $request){
        return Booking::where('user_id', $request->user()->id)
            ->with('room')
            ->orderByDesc('created_at')
            ->get();
    }

    


    public function index()
    {
        Booking::where('status', 'paid')
            ->whereDate('check_out_date', '<', now())
            ->update(['status' => 'completed']);
        return Booking::with([
            'user:id,name',
            'room:id,name',

        ])
        ->orderByDesc('created_at')
        ->get();
    }



    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending_payment') {
            return response()->json([
                'message' => 'Only pending booking can be cancelled'
            ], 422);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);

            RoomBlock::where('room_id', $booking->room_id)
                ->where('reason', 'booking')
                ->where('start_date', $booking->check_in_date)
                ->where('end_date', $booking->check_out_date)
                ->delete();
        });

        return response()->json([
            'message' => 'Booking cancelled'
        ]);
    }

    public function complete($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'paid') {
            return response()->json([
                'message' => 'Only paid booking can be completed'
            ], 422);
        }

        $booking->update([
            'status' => 'completed'
        ]);

        return response()->json([
            'message' => 'Booking marked as completed'
        ]);
    }

    public function markPaid($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending_payment') {
            return response()->json([
                'message' => 'Only pending booking can be marked as paid'
            ], 422);
        }

        $booking->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json([
            'message' => 'Booking marked as paid'
        ]);
    }

}
