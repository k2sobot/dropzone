<?php

namespace App\Console\Commands;

use App\Models\SystemLog;
use App\Services\FileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SystemCronCommand extends Command
{
    protected $signature = 'system:cron';

    protected $description = 'Run system cron tasks';

    public function handle(FileService $fileService): int
    {
        $start = microtime(true);

        SystemLog::info('Cron job started');
        Cache::forever('system_cron_last_run', time());

        $deleted = $fileService->cleanupExpired();
        if ($deleted > 0) {
            SystemLog::info("Cleaned up {$deleted} expired or downloaded uploads");
        }

        $this->cleanupOldLogs();

        $duration = round((microtime(true) - $start) * 1000, 2);
        SystemLog::info('Cron job completed', ['duration_ms' => $duration]);
        $this->info("Cron completed in {$duration}ms");

        return Command::SUCCESS;
    }

    private function cleanupOldLogs(): void
    {
        $deleted = SystemLog::where('created_at', '<', now()->subDays(30))->delete();

        if ($deleted > 0) {
            SystemLog::info("Cleaned up {$deleted} old log entries");
        }
    }
}
