<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_vip_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->unsignedInteger('duration_days');
            $table->unsignedInteger('price');
            $table->string('description', 500)->nullable();
            $table->string('badge', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'code'], 'category_vip_packages_category_code_unique');
            $table->index(['category_id', 'is_active'], 'category_vip_packages_category_active_idx');
        });

        $this->seedDefaultPackages();
    }

    public function down(): void
    {
        Schema::dropIfExists('category_vip_packages');
    }

    private function seedDefaultPackages(): void
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        $defaultCategory = DB::table('categories')
            ->where('slug', config('vip.default_category_slug', 'dracin'))
            ->first(['id']);

        if (!$defaultCategory) {
            return;
        }

        $existing = DB::table('category_vip_packages')
            ->where('category_id', $defaultCategory->id)
            ->count();

        if ($existing > 0) {
            return;
        }

        $packages = config('vip.packages', []);
        $sort = 0;

        foreach ($packages as $code => $package) {
            DB::table('category_vip_packages')->insert([
                'category_id' => $defaultCategory->id,
                'code' => (string) $code,
                'name' => $package['name'] ?? $code,
                'duration_days' => (int) ($package['duration'] ?? 0),
                'price' => (int) ($package['price'] ?? 0),
                'description' => $package['description'] ?? null,
                'badge' => $package['badge'] ?? null,
                'is_active' => true,
                'sort_order' => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
