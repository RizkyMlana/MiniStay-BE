<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            ['Single Budget', 150000, 1],
            ['Single Deluxe', 220000, 1],
            ['Double Standard', 280000, 2],
            ['Double Deluxe', 350000, 2],
            ['Family Room', 450000, 4],
            ['Studio Room', 300000, 2],
            ['VIP Room', 600000, 2],
            ['Economy Room', 130000, 1],
            ['Executive Room', 500000, 2],
            ['Penthouse Mini', 750000, 3],
        ];

        foreach ($rooms as $room) {
            Room::create([
                'name' => $room[0],
                'description' => "Kamar {$room[0]} dengan fasilitas lengkap",
                'price_per_day' => $room[1],
                'capacity' => $room[2],
            ]);
        }
    }
}
