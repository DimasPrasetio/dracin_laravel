<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditDataIntegrityCommand extends Command
{
    protected $signature = 'data:audit {--details : Show sample rows for each issue}';
    protected $description = 'Audit duplicates and orphaned references before running migrations';

    public function handle(): int
    {
        $details = (bool) $this->option('details');
        $issues = 0;

        $this->info('Data integrity audit');

        $report = function (string $label, int $count, ?array $samples = null) use (&$issues, $details): void {
            if ($count === 0) {
                $this->line("OK   {$label}: 0");
                return;
            }

            $issues++;
            $this->warn("WARN {$label}: {$count}");

            if ($details && $samples) {
                foreach ($samples as $sample) {
                    $this->line('  - ' . $sample);
                }
            }
        };

        $this->auditUserDuplicates($report, $details);
        $this->auditCategoryAdmins($report, $details);
        $this->auditCategoryVip($report, $details);
        $this->auditMovies($report, $details);
        $this->auditVideoParts($report, $details);
        $this->auditPayments($report, $details);
        $this->auditViewLogs($report, $details);

        if ($issues === 0) {
            $this->info('No issues detected.');
            return self::SUCCESS;
        }

        $this->warn('Audit completed with warnings. Review before migrating.');
        return self::FAILURE;
    }

    private function auditUserDuplicates(callable $report, bool $details): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $emailDupes = DB::table('users')
            ->whereNotNull('email')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1);

        $report('users.email duplicates', (clone $emailDupes)->get()->count(), $details ? $this->sampleDuplicates($emailDupes, 'email') : null);

        $usernameDupes = DB::table('users')
            ->whereNotNull('username')
            ->select('username', DB::raw('COUNT(*) as count'))
            ->groupBy('username')
            ->having('count', '>', 1);

        $report('users.username duplicates', (clone $usernameDupes)->get()->count(), $details ? $this->sampleDuplicates($usernameDupes, 'username') : null);

        if (Schema::hasColumn('users', 'telegram_id')) {
            $telegramDupes = DB::table('users')
                ->whereNotNull('telegram_id')
                ->select('telegram_id', DB::raw('COUNT(*) as count'))
                ->groupBy('telegram_id')
                ->having('count', '>', 1);

            $report('users.telegram_id duplicates', (clone $telegramDupes)->get()->count(), $details ? $this->sampleDuplicates($telegramDupes, 'telegram_id') : null);
        }
    }

    private function auditCategoryAdmins(callable $report, bool $details): void
    {
        if (!Schema::hasTable('category_admins')) {
            return;
        }

        $missingCategory = DB::table('category_admins as ca')
            ->leftJoin('categories as c', 'ca.category_id', '=', 'c.id')
            ->whereNull('c.id');

        $report('category_admins.category_id orphan', $missingCategory->count(), $details ? $this->sampleOrphans($missingCategory, ['category_id', 'user_id'], 'ca.id') : null);

        if (Schema::hasColumn('category_admins', 'user_id')) {
            $missingUser = DB::table('category_admins as ca')
                ->leftJoin('users as u', 'ca.user_id', '=', 'u.id')
                ->whereNotNull('ca.user_id')
                ->whereNull('u.id');

            $report('category_admins.user_id orphan', $missingUser->count(), $details ? $this->sampleOrphans($missingUser, ['user_id', 'category_id'], 'ca.id') : null);
        }
    }

    private function auditCategoryVip(callable $report, bool $details): void
    {
        if (!Schema::hasTable('user_category_vip')) {
            return;
        }

        if (Schema::hasColumn('user_category_vip', 'user_id')) {
            $missingUser = DB::table('user_category_vip as ucv')
                ->leftJoin('users as u', 'ucv.user_id', '=', 'u.id')
                ->whereNotNull('ucv.user_id')
                ->whereNull('u.id');

            $report('user_category_vip.user_id orphan', $missingUser->count(), $details ? $this->sampleOrphans($missingUser, ['user_id', 'category_id'], 'ucv.id') : null);
        }

        $missingCategory = DB::table('user_category_vip as ucv')
            ->leftJoin('categories as c', 'ucv.category_id', '=', 'c.id')
            ->whereNull('c.id');

        $report('user_category_vip.category_id orphan', $missingCategory->count(), $details ? $this->sampleOrphans($missingCategory, ['category_id', 'user_id'], 'ucv.id') : null);
    }

    private function auditMovies(callable $report, bool $details): void
    {
        if (!Schema::hasTable('movies') || !Schema::hasColumn('movies', 'category_id')) {
            return;
        }

        $missingCategory = DB::table('movies as m')
            ->leftJoin('categories as c', 'm.category_id', '=', 'c.id')
            ->whereNotNull('m.category_id')
            ->whereNull('c.id');

        $report('movies.category_id orphan', $missingCategory->count(), $details ? $this->sampleOrphans($missingCategory, ['category_id'], 'm.id') : null);
    }

    private function auditVideoParts(callable $report, bool $details): void
    {
        if (!Schema::hasTable('video_parts')) {
            return;
        }

        $missingMovie = DB::table('video_parts as vp')
            ->leftJoin('movies as m', 'vp.movie_id', '=', 'm.id')
            ->whereNull('m.id');

        $report('video_parts.movie_id orphan', $missingMovie->count(), $details ? $this->sampleOrphans($missingMovie, ['movie_id'], 'vp.id') : null);
    }

    private function auditPayments(callable $report, bool $details): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        if (Schema::hasColumn('payments', 'user_id')) {
            $missingUser = DB::table('payments as p')
                ->leftJoin('users as u', 'p.user_id', '=', 'u.id')
                ->whereNotNull('p.user_id')
                ->whereNull('u.id');

            $report('payments.user_id orphan', $missingUser->count(), $details ? $this->sampleOrphans($missingUser, ['user_id', 'category_id'], 'p.id') : null);
        }

        if (Schema::hasColumn('payments', 'category_id')) {
            $missingCategory = DB::table('payments as p')
                ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
                ->whereNotNull('p.category_id')
                ->whereNull('c.id');

            $report('payments.category_id orphan', $missingCategory->count(), $details ? $this->sampleOrphans($missingCategory, ['category_id'], 'p.id') : null);
        }
    }

    private function auditViewLogs(callable $report, bool $details): void
    {
        if (!Schema::hasTable('view_logs')) {
            return;
        }

        if (Schema::hasColumn('view_logs', 'user_id')) {
            $missingUser = DB::table('view_logs as vl')
                ->leftJoin('users as u', 'vl.user_id', '=', 'u.id')
                ->whereNotNull('vl.user_id')
                ->whereNull('u.id');

            $report('view_logs.user_id orphan', $missingUser->count(), $details ? $this->sampleOrphans($missingUser, ['user_id', 'category_id'], 'vl.id') : null);
        }

        if (Schema::hasColumn('view_logs', 'category_id')) {
            $missingCategory = DB::table('view_logs as vl')
                ->leftJoin('categories as c', 'vl.category_id', '=', 'c.id')
                ->whereNotNull('vl.category_id')
                ->whereNull('c.id');

            $report('view_logs.category_id orphan', $missingCategory->count(), $details ? $this->sampleOrphans($missingCategory, ['category_id'], 'vl.id') : null);
        }
    }

    private function sampleDuplicates($query, string $column): array
    {
        return $query
            ->select($column . ' as value', DB::raw('COUNT(*) as count'))
            ->groupBy($column)
            ->limit(5)
            ->get()
            ->map(fn ($row) => "{$column}={$row->value} (count={$row->count})")
            ->all();
    }

    private function sampleOrphans($query, array $columns, string $idColumn): array
    {
        $selects = [$idColumn . ' as id'];

        foreach ($columns as $column) {
            $selects[] = $column . ' as ' . $column;
        }

        return $query
            ->select($selects)
            ->limit(5)
            ->get()
            ->map(function ($row) use ($columns) {
                $parts = ['id=' . $row->id];
                foreach ($columns as $column) {
                    $parts[] = $column . '=' . ($row->{$column} ?? 'null');
                }
                return implode(', ', $parts);
            })
            ->all();
    }
}
