<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_category_vip') || !Schema::hasColumn('user_category_vip', 'user_id')) {
            return;
        }

        Schema::table('user_category_vip', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('user_category_vip', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_category_vip') || !Schema::hasColumn('user_category_vip', 'user_id')) {
            return;
        }

        Schema::table('user_category_vip', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('user_category_vip', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
