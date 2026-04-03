<?php

namespace App\Console\Commands;

use App\Services\FileService;
use Illuminate\Console\Command;

class CleanupExpiredUploads extends Command
{
    protected $signature = 'uploads:cleanup';

    protected $description = 'Clean up expired and downloaded files';

    public function handle(FileService $fileService): int
    {
        $this->info('Cleaning up expired uploads...');

        $deleted = $fileService->cleanupExpired();

        $this->info("Deleted {$deleted} expired uploads.");

        return self::SUCCESS;
    }
}
