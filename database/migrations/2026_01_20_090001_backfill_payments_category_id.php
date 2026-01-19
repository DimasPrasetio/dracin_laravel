<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments') || !Schema::hasColumn('payments', 'category_id')) {
            return;
        }

        $defaultSlug = config('vip.default_category_slug', 'dracin');
        $defaultCategory = DB::table('categories')
            ->where('slug', $defaultSlug)
            ->first(['id']);

        if (!$defaultCategory) {
            return;
        }

        DB::table('payments')
            ->whereNull('category_id')
            ->update(['category_id' => $defaultCategory->id]);
    }

    public function down(): void
    {
        // No rollback: backfill should remain.
    }
};
