<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Room::all() as $room) {
            $imageCount = rand(2, 4);

            for ($i = 1; $i <= $imageCount; $i++) {
                RoomImage::create([
                    'room_id' => $room->id,
                    'path' => "rooms/room_{$room->id}_{$i}.jpg",
                    'is_cover' => $i === 1,
                ]);
            }
        }
    }
}
