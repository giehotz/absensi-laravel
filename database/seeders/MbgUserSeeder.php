<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MbgUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'petugas.mbg@sekolah.id'],
            [
                'name' => 'Petugas Makan Bergizi',
                'password' => Hash::make('password'),
                'role' => 'petugas_mbg',
                'is_active' => true,
            ]
        );
    }
}
