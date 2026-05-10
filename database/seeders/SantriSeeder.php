<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SantriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'santri@thursina.id'],
            [
                'name' => 'User Santri',
                'password' => \Illuminate\Support\Facades\Hash::make('santri'),
                'role' => 'santri',
            ]
        );

        \App\Models\Santri::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nama' => 'User Santri',
                'kelas' => '10A',
                'foto_referensi' => 'default.jpg',
                'face_descriptor' => '[]',
            ]
        );
    }
}
