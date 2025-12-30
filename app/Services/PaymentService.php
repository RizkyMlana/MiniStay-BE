<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function createPayment(Booking $booking): Payment
    {
        if ($booking->status !== 'pending') {
            throw new \Exception('Payment can only be created for pending booking');
        }

        return Payment::create([
            'booking_id' => $booking->id,
            'amount'     => $booking->total_price,
            'method'     => 'bank_transfer',
            'status'     => 'pending',
        ]);
    }

    public function confirmPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
             $payment->update([
                'status' => 'confirmed',
            ]);

            $payment->booking->update([
                'status'   => 'paid',
            '   qr_token' => Str::random(48),
            ]);
        });
    }

    public function rejectPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'rejected',
            ]);

            $payment->booking->update([
                'status' => 'cancelled',
            ]);
        });
    }
}