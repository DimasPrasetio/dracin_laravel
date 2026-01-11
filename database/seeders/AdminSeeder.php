<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@dracin.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Username: admin');
        $this->command->info('Password: admin123');
    }
}
