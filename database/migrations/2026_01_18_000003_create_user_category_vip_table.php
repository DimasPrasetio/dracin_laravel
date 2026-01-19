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
     * This table stores VIP subscriptions per category.
     * A user can have different VIP expiry dates for different categories.
     *
     * Example:
     * - User A: VIP Drakor until 2026-02-01
     * - User A: VIP Anime until 2026-01-25
     * - User B: VIP Drakor until 2026-03-01
     */
    public function up(): void
    {
        Schema::create('user_category_vip', function (Blueprint $table) {
            $table->id();

            // User reference
            $table->foreignId('telegram_user_id')
                ->constrained('telegram_users')
                ->cascadeOnDelete();

            // Category reference
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            // VIP expiry date for this category
            $table->dateTime('vip_until');

            $table->timestamps();

            // One VIP subscription per user per category
            $table->unique(['telegram_user_id', 'category_id'], 'unique_user_category_vip');

            // Indexes for faster queries
            $table->index('telegram_user_id');
            $table->index('category_id');
            $table->index('vip_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_category_vip');
    }
};
