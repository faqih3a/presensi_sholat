<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AsatidzSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@thursina.id'],
            [
                'name' => 'Admin Asatidz',
                'password' => \Illuminate\Support\Facades\Hash::make('admin'),
                'role' => 'asatidz',
            ]
        );
    }
}
