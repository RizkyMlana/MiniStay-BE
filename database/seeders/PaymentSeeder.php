<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::all();

        foreach ($bookings as $booking) {

            // Booking belum bayar → belum tentu ada payment
            if ($booking->status === 'pending_payment') {
                if (rand(1, 100) <= 60) {
                    continue;
                }
            }

            $paymentStatus = null;
            $confirmedBy = null;
            $confirmedAt = null;

            switch ($booking->status) {
                case 'waiting_confirmation':
                    $paymentStatus = 'pending';
                    break;

                case 'paid':
                case 'completed':
                    $paymentStatus = 'confirmed';
                    $confirmedBy = 1; // admin
                    $confirmedAt = Carbon::now()->subDays(rand(1, 3));
                    break;

                case 'cancelled':
                    $paymentStatus = 'rejected';
                    break;

                default:
                    continue 2;
            }

            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'method' => 'manual_transfer',
                'status' => $paymentStatus,
                'confirmed_by' => $confirmedBy,
                'confirmed_at' => $confirmedAt,
            ]);
        }
    }
}
