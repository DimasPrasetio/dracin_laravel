<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'free_parts',
                'value' => '1',
                'description' => 'Part numbers that are free for basic users (comma separated)',
                'updated_at' => now(),
            ],
            [
                'key' => 'channel_post_footer',
                'value' => "📌 Informasi Penting:\n• Ketik /vip untuk upgrade akses premium\n• Join @dracin_hd untuk update film terbaru\n• Butuh bantuan? Ketik /help",
                'description' => 'Footer message for channel posts',
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
