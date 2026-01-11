<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comments = [
            'Kamar bersih dan nyaman',
            'Sesuai foto',
            'Harga worth it',
            'Pelayanan ramah',
            'Akan booking lagi',
        ];

        $bookings = Booking::where('status', 'completed')->get();

        foreach ($bookings as $booking) {
            Rating::create([
                'booking_id' => $booking->id,
                'rating' => rand(3, 5),
                'comment' => $comments[array_rand($comments)],
                'is_visible' => true,
            ]);
        }
    }
}
