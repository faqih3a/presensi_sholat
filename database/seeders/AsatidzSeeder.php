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
        \App\Models\User::create([
            'name' => 'Admin Asatidz',
            'email' => 'admin@thursina.id',
            'password' => \Illuminate\Support\Facades\Hash::make('admin'),
            'role' => 'asatidz',
        ]);
    }
}
