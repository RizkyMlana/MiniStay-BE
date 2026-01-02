<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function confirm(Payment $payment){
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'confirmed']);
            $payment->booking->update([
                'status' => 'paid',
                'qr_token' => Str::random(48),
            ]);
        });

        WhatsApp::send(
            $payment->booking->user->phone,
            "Pembayaran booking {$payment->booking->booking_code} dikonfirmasi."
        );

        return response()->json(['message' => 'Payment Confirmed']);
    }
}
