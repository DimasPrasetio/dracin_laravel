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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'video_part_id')) {
                $table->foreignId('video_part_id')
                    ->nullable()
                    ->after('telegram_user_id')
                    ->constrained('video_parts')
                    ->nullOnDelete();
            }
        });

        if (!Schema::hasTable('view_logs')) {
            Schema::create('view_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('telegram_user_id')->constrained('telegram_users')->cascadeOnDelete();
                $table->foreignId('video_part_id')->constrained('video_parts')->cascadeOnDelete();
                $table->boolean('is_vip')->default(false);
                $table->string('source', 100)->nullable();
                $table->string('device', 100)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index('is_vip');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('view_logs')) {
            Schema::dropIfExists('view_logs');
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'video_part_id')) {
                $table->dropForeign(['video_part_id']);
                $table->dropColumn('video_part_id');
            }
        });
    }
};
