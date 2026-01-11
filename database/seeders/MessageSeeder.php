<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            'Apakah ada parkir?',
            'Kamar ini ada AC?',
            'Bisa check-in malam?',
            'Ada air panas?',
            'Dekat minimarket?',
        ];

        foreach ($questions as $q) {
            Message::create([
                'sender_type' => 'user',
                'sender_id' => rand(2, 11),
                'room_id' => rand(1, 10),
                'booking_id' => null,
                'content' => $q,
            ]);

            Message::create([
                'sender_type' => 'admin',
                'sender_id' => 1,
                'room_id' => rand(1, 10),
                'booking_id' => null,
                'content' => 'Iya, tersedia dan siap digunakan.',
            ]);
        }
    }
}
