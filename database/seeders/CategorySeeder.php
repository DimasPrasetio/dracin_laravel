<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'name' => 'Dracin',
            'slug' => 'dracin',
            'description' => 'Kategori utama untuk film drama China',
            'bot_token' => config('telegram.bots.default.token', env('TELE_BOT_TOKEN', 'PLEASE_UPDATE_BOT_TOKEN')),
            'bot_username' => config('telegram.bots.default.username', env('TELE_BOT_USERNAME', '@default_bot')),
            'channel_id' => config('telegram.bots.default.channel_id', env('TELE_CHANNEL_ID')),
            'is_active' => true,
        ];

        Category::updateOrCreate(
            ['slug' => $data['slug']],
            array_merge($data, ['webhook_secret' => Str::random(32)])
        );
    }
}
