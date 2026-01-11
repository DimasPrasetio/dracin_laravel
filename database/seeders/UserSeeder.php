<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'name' => 'Admin',
            'phone' => '081234567890',
            'email' => 'admin@yopmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    }
}