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
     *
     * This table manages admin permissions per category:
     * - user_id: For web admin access
     * - telegram_user_id: For bot admin access
     * - role: 'admin' can do everything, 'moderator' can only add movies
     */
    public function up(): void
    {
        Schema::create('category_admins', function (Blueprint $table) {
            $table->id();

            // Category reference
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            // Web admin (nullable - can be bot-only admin)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            // Telegram admin (nullable - can be web-only admin)
            $table->foreignId('telegram_user_id')
                ->nullable()
                ->constrained('telegram_users')
                ->cascadeOnDelete();

            // Role within category
            $table->enum('role', ['admin', 'moderator'])->default('moderator');

            $table->timestamps();

            // Unique constraints to prevent duplicate assignments
            $table->unique(['category_id', 'user_id'], 'unique_category_web_admin');
            $table->unique(['category_id', 'telegram_user_id'], 'unique_category_telegram_admin');

            // Indexes for faster queries
            $table->index('category_id');
            $table->index('user_id');
            $table->index('telegram_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_admins');
    }
};
