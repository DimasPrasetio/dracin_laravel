<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Important: TelegramAdminSeeder must run first
        // so telegram users exist before linking in UserSeeder
        $this->call([
            CategorySeeder::class,
            TelegramAdminSeeder::class,
            UserSeeder::class,
        ]);
    }
}
