<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function submit(Request $request, $bookingId){
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        if($booking->status !== 'pending_request') {
            return response()->json([
                'message' => 'Booking not eligible for payment'
            ], 422);
        }

        DB::transaction(function () use ($booking) {
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'method' => 'manual_transfer',
                'status' => 'pending',
            ]);

            $booking->update([
                'status' => 'waiting_confirmation',
            ]);
        });

        return response()->json([
            'message' => 'Payment submitted, waiting confirmation'
        ]);
    }

    public function index(){
        return Payment::with(['booking.user', 'booking.room'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function confirm(Request $request, $id){
        $payment = Payment::findOrFail($id);
        if ($payment->status !== 'pending') {
            return response()->json([
                'message' => 'Payment already processed'
            ], 422);
        }

        DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'status'       => 'confirmed',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);

            $payment->booking->update([
                'status' => 'paid',
            ]);
        });

        return response()->json([
            'message' => 'Payment confirmed'
        ]);
    }

    public function reject($id){
        $payment = Payment::findOrFail($id);
        if ($payment->status !== 'pending') {
            return response()->json([
                'message' => 'Payment already processed'
            ], 422);
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'rejected',
            ]);

            $payment->booking->update([
                'status' => 'cancelled',
            ]);
        });

        return response()->json([
            'message' => 'Payment rejected'
        ]);
    }
}
