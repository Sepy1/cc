<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleUsersSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'sekper@example.com'],
            [
                'name' => 'Sekretaris Perusahaan',
                'password' => Hash::make('password123'), // ganti password jika perlu
                'role' => 'admin',
            ]
        );

        // Officer
        User::updateOrCreate(
            ['email' => 'kepatuhan@example.com'],
            [
                'name' => 'Satuan Kerja Kepatuhan',
                'password' => Hash::make('password123'),
                'role' => 'officer',
            ]
        );

         // Officer 2
        User::updateOrCreate(
            ['email' => 'pemasaran@example.com'],
            [
                'name' => 'Divisi Pemasaran',
                'password' => Hash::make('password123'),
                'role' => 'officer',
            ]
        );

         // Officer 2
        User::updateOrCreate(
            ['email' => 'dpk@example.com'],
            [
                'name' => 'Divisi Penyelesaian Kredit',
                'password' => Hash::make('password123'),
                'role' => 'officer',
            ]
        );
    }
}
