<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Users
        User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '08123456789',
                'address' => 'Jl. Admin No. 1',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin1@mail.com'],
            [
                'name' => 'Administrator 1',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '08123456788',
                'address' => 'Jl. Admin No. 2',
            ]
        );

        // ...existing staff and customer seeds...
    }
}
