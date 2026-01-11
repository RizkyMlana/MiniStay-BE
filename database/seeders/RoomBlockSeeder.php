<?php

namespace Database\Seeders;

use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($roomId = 1; $roomId <= 3; $roomId++) {
            RoomBlock::create([
                'room_id' => $roomId,
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(7),
                'reason' => 'Maintenance',
            ]);
        }
    }
}
