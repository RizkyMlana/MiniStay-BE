<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $names = [
            'Andi', 'Budi', 'Citra', 'Dewi', 'Eka',
            'Fajar', 'Gina', 'Hendra', 'Indah', 'Joko',
        ];

        foreach ($names as $i => $name) {
            User::create([
                'name' => $name,
                'phone' => '62812' . rand(10000000, 99999999),
                'password' => Hash::make('password'),
                'role' => 'user',
            ]);
        }
    }
}
