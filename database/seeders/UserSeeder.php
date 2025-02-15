<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserRole;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate([
            'email' => 'admin@example.com'
        ], [
            'nama_lengkap' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => UserRole::Admin->value,
            'profile_picture' => 'default.jpg'
        ]);

        User::updateOrCreate([
            'email' => 'karyawan@example.com'
        ], [
            'nama_lengkap' => 'Karyawan User',
            'username' => 'karyawan',
            'password' => Hash::make('password123'),
            'role' => UserRole::Karyawan->value,
            'profile_picture' => 'default.jpg'
        ]);
    }
}
