<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        \App\Console\Commands\CleanupExpiredUploads::class,
        \App\Console\Commands\ExtensionInstallCommand::class,
        \App\Console\Commands\ExtensionUninstallCommand::class,
        \App\Console\Commands\ExtensionUpdateCommand::class,
        \App\Console\Commands\ExtensionListCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        $schedule->command('uploads:cleanup')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}