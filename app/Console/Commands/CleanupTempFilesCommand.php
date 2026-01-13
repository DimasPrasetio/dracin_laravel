<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupTempFilesCommand extends Command
{
    protected $signature = 'storage:cleanup-temp {--dry-run : List files without deleting them}';

    protected $description = 'Remove expired QRIS and temporary assets to avoid storage leaks';

    public function handle(): int
    {
        $directories = array_filter(config('vip.cleanup.directories', []));
        $maxAgeMinutes = (int) config('vip.cleanup.max_age_minutes', 180);
        $cutoff = Carbon::now()->subMinutes($maxAgeMinutes);
        $dryRun = (bool) $this->option('dry-run');

        $totalDeleted = 0;

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $totalDeleted += $this->cleanupDirectory($directory, $cutoff, $dryRun);
        }

        if ($dryRun) {
            $this->info("Dry run complete. {$totalDeleted} file(s) would be removed.");
        } else {
            $this->info("Temp cleanup finished. {$totalDeleted} file(s) removed.");
            Log::info('Temporary file cleanup executed', [
                'deleted' => $totalDeleted,
                'cutoff' => $cutoff->toDateTimeString(),
            ]);
        }

        return Command::SUCCESS;
    }

    private function cleanupDirectory(string $directory, Carbon $cutoff, bool $dryRun): int
    {
        $deleted = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if ($fileInfo->getFilename() === '.gitignore') {
                continue;
            }

            if ($fileInfo->getMTime() >= $cutoff->getTimestamp()) {
                continue;
            }

            $deleted++;

            if ($dryRun) {
                $this->line('[dry-run] ' . $fileInfo->getPathname());
                continue;
            }

            @unlink($fileInfo->getPathname());
        }

        return $deleted;
    }
}
