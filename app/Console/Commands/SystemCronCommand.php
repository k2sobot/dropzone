<?php

namespace App\Console\Commands;

use App\Models\SystemLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SystemCronCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:cron';

    /**
     * The console command description.
     */
    protected $description = 'Run system cron tasks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $start = microtime( true );

        // Log cron execution start
        SystemLog::info( 'Cron job started' );
        Cache::forever( 'system_cron_last_run', time() );

        // Clean up expired uploads
        $this->cleanupExpiredUploads();

        // Clean up old logs (older than 30 days)
        $this->cleanupOldLogs();

        $duration = round( ( microtime( true ) - $start ) * 1000, 2 );

        SystemLog::info( 'Cron job completed', [ 'duration_ms' => $duration ] );

        $this->info( "Cron completed in {$duration}ms" );

        return Command::SUCCESS;
    }

    /**
     * Clean up expired uploads.
     */
    private function cleanupExpiredUploads(): void
    {
        $expired = \App\Models\Upload::where( 'expires_at', '<', now() )
            ->where( 'status', 'active' )
            ->get();

        foreach ( $expired as $upload ) {
            $upload->update( [ 'status' => 'expired' ] );

            // Delete the file
            $path = storage_path( 'app/uploads/' . $upload->filename );
            if ( file_exists( $path ) ) {
                unlink( $path );
                SystemLog::info( 'Expired file deleted', [
                    'upload_id' => $upload->id,
                    'filename' => $upload->original_name,
                ] );
            }
        }

        if ( $expired->count() > 0 ) {
            SystemLog::info( "Cleaned up {$expired->count()} expired uploads" );
        }
    }

    /**
     * Clean up old logs.
     */
    private function cleanupOldLogs(): void
    {
        $deleted = SystemLog::where( 'created_at', '<', now()->subDays( 30 ) )->delete();

        if ( $deleted > 0 ) {
            SystemLog::info( "Cleaned up {$deleted} old log entries" );
        }
    }
}
