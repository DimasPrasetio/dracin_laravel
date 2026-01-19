<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SAFE MIGRATION: Creates new table only, no existing data affected
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();

            // Telegram Bot Configuration
            $table->string('bot_token', 100);
            $table->string('bot_username', 100);
            $table->string('channel_id', 100)->nullable()->comment('Telegram channel ID for posting movies');
            $table->string('webhook_secret', 100)->nullable()->comment('Secret for webhook verification');

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->environment('production')) {
            return;
        }

        Schema::dropIfExists('categories');
    }
};
