<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            // Add role column for bot-specific permissions
            $table->string('role', 50)->default('user')->after('last_name');

            // Add optional link to web users table
            $table->foreignId('linked_user_id')
                ->nullable()
                ->after('role')
                ->constrained('users')
                ->nullOnDelete();

            // Add indexes for performance
            $table->index('role');
            $table->index('linked_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropForeign(['linked_user_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['linked_user_id']);
            $table->dropColumn(['role', 'linked_user_id']);
        });
    }
};
