<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SAFE MIGRATION: Only adds NULLABLE columns to existing tables
     * - No data will be lost
     * - Existing records will have NULL category_id initially
     * - Data migration happens in the next migration file
     */
    public function up(): void
    {
        // Add category_id to movies table
        Schema::table('movies', function (Blueprint $table) {
            if (!Schema::hasColumn('movies', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('categories')
                    ->nullOnDelete();

                $table->index('category_id');
            }
        });

        // Add category_id to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('video_part_id')
                    ->constrained('categories')
                    ->nullOnDelete();

                $table->index('category_id');
            }
        });

        // Add category_id to view_logs table
        if (Schema::hasTable('view_logs')) {
            Schema::table('view_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('view_logs', 'category_id')) {
                    $table->foreignId('category_id')
                        ->nullable()
                        ->after('video_part_id')
                        ->constrained('categories')
                        ->nullOnDelete();

                    $table->index('category_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->environment('production')) {
            return;
        }

        // Remove from view_logs
        if (Schema::hasTable('view_logs') && Schema::hasColumn('view_logs', 'category_id')) {
            Schema::table('view_logs', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropIndex(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        // Remove from payments
        if (Schema::hasColumn('payments', 'category_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropIndex(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        // Remove from movies
        if (Schema::hasColumn('movies', 'category_id')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropIndex(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
};
