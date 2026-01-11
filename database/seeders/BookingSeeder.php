<?php

namespace Database\Seeders;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'pending_payment',
            'waiting_confirmation',
            'paid',
            'completed',
            'cancelled',
        ];

        for ($i = 1; $i <= 12; $i++) {
            $checkIn = Carbon::now()->addDays(rand(-5, 10));
            $checkOut = (clone $checkIn)->addDays(rand(1, 4));

            Booking::create([
                'booking_code' => 'MS-' . strtoupper(Str::random(8)),
                'user_id' => rand(2, 11),
                'room_id' => rand(1, 10),
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'status' => $statuses[array_rand($statuses)],
                'total_price' => rand(300000, 2000000),
                'payment_deadline' => Carbon::now()->addHours(24),
            ]);
        }
    }
}
