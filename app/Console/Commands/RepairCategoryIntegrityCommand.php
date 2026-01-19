<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RepairCategoryIntegrityCommand extends Command
{
    protected $signature = 'data:repair-categories {--default=dracin : Default category slug} {--apply : Apply changes (otherwise dry-run)}';
    protected $description = 'Repair missing/invalid category_id by assigning the default category';

    public function handle(): int
    {
        if (!Schema::hasTable('categories')) {
            $this->error('categories table not found.');
            return self::FAILURE;
        }

        $slug = (string) $this->option('default');
        $apply = (bool) $this->option('apply');
        $defaultCategory = DB::table('categories')->where('slug', $slug)->first();

        if (!$defaultCategory && $apply) {
            $defaultCategoryId = DB::table('categories')->insertGetId([
                'name' => 'Dracin',
                'slug' => $slug,
                'description' => 'Kategori utama untuk film drama China',
                'bot_token' => config('telegram.bots.default.token', env('TELE_BOT_TOKEN', 'PLEASE_UPDATE_BOT_TOKEN')),
                'bot_username' => config('telegram.bots.default.username', env('TELE_BOT_USERNAME', '@default_bot')),
                'channel_id' => config('telegram.bots.default.channel_id', env('TELE_CHANNEL_ID')),
                'webhook_secret' => Str::random(32),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $defaultCategory = (object) ['id' => $defaultCategoryId, 'slug' => $slug];
            $this->info("Created default category with slug '{$slug}'.");
        }

        if (!$defaultCategory) {
            $this->warn("Default category with slug '{$slug}' not found. Run with --apply to create it.");
        }

        $defaultId = $defaultCategory?->id;

        $this->repairCategoryId('movies', $defaultId, $apply);
        $this->repairCategoryId('payments', $defaultId, $apply);
        $this->repairCategoryId('view_logs', $defaultId, $apply);
        $this->repairCategoryId('user_category_vip', $defaultId, $apply);

        $this->info($apply ? 'Repair completed.' : 'Dry-run completed. Re-run with --apply to update data.');
        return self::SUCCESS;
    }

    private function repairCategoryId(string $table, ?int $defaultId, bool $apply): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'category_id')) {
            return;
        }

        $nullCount = DB::table($table)->whereNull('category_id')->count();
        $orphanCount = DB::table($table)
            ->whereNotNull('category_id')
            ->whereNotIn('category_id', DB::table('categories')->select('id'))
            ->count();

        $this->line("{$table}.category_id null: {$nullCount}, orphan: {$orphanCount}");

        if (!$apply || !$defaultId) {
            return;
        }

        if ($nullCount > 0) {
            DB::table($table)->whereNull('category_id')->update(['category_id' => $defaultId]);
        }

        if ($orphanCount > 0) {
            DB::table($table)
                ->whereNotNull('category_id')
                ->whereNotIn('category_id', DB::table('categories')->select('id'))
                ->update(['category_id' => $defaultId]);
        }
    }
}
