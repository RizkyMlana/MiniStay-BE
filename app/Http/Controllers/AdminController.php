<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminController extends Controller
{    

    public function listBooking(){
        $booking = Booking::with('user', 'room')
            ->latest()
            ->get();
        return response()->json($booking);
    }

    public function showBooking($id){
        $data = Booking::with(['user', 'room', 'payment'])->findOrFail($id);
        return response()->json($data);
    }



    public function updateBookingStatus($id, Request $request){
        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,completed,cancelled'
        ]);

        $booking = Booking::findOrFail($id);
        $booking->status = $request->status;
        $booking->save();

        return response()->json(['message' => 'Status updated', 'data' => $booking]);
    }

    public function cancelBooking($id){
        $booking = Booking::findOrFail($id);
        $booking->status = 'cancelled';
        $booking->save();

        return response()->json(['message' => 'Booking Cancelled']);
    }


    public function confirmPayment($id){
        $payment = Payment::findOrFail($id);
        $payment->status = 'paid';
        $payment->paid_at = now();
        $payment->save();

        $booking = Booking::find($payment->booking_id);
        $booking->status = 'paid';
        $booking->save();

        return response()->json(['message' => 'Payment Confirmed']);
    }
}
