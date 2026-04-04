<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Output\BufferedOutput;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class SystemController extends Controller
{
    /**
     * System status dashboard.
     */
    public function status()
    {
        // Server info
        $server_info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => config( 'database.default' ),
            'cache_driver' => config( 'cache.default' ),
            'queue_driver' => config( 'queue.default' ),
        ];

        // Storage info
        $storage_path = storage_path();
        $storage_writable = is_writable( $storage_path );
        $storage_size = $this->getDirectorySize( $storage_path );

        // Uploads info
        $uploads_count = Upload::count();
        $uploads_size = Upload::sum( 'size' ) ?: 0;

        // System logs count
        $logs_count = SystemLog::count();
        $error_count = SystemLog::errors()->count();

        // Check cron last run
        $last_cron = Cache::get( 'system_cron_last_run' );
        $cron_status = $last_cron ? ( time() - $last_cron < 3600 ? 'ok' : 'warning' ) : 'unknown';

        // Memory usage
        $memory_usage = $this->formatBytes( memory_get_usage( true ) );
        $memory_peak = $this->formatBytes( memory_get_peak_usage( true ) );

        // Disk space
        $disk_free = $this->formatBytes( disk_free_space( base_path() ) );
        $disk_total = $this->formatBytes( disk_free_space( base_path() ) + ( disk_total_space( base_path() ) - disk_free_space( base_path() ) ) );

        // Extensions
        $extensions = [
            'pdo' => extension_loaded( 'pdo' ),
            'pdo_sqlite' => extension_loaded( 'pdo_sqlite' ),
            'mbstring' => extension_loaded( 'mbstring' ),
            'openssl' => extension_loaded( 'openssl' ),
            'tokenizer' => extension_loaded( 'tokenizer' ),
            'xml' => extension_loaded( 'xml' ),
            'ctype' => extension_loaded( 'ctype' ),
            'json' => extension_loaded( 'json' ),
            'gd' => extension_loaded( 'gd' ),
            'fileinfo' => extension_loaded( 'fileinfo' ),
        ];

        return view( 'admin.system.status', compact(
            'server_info',
            'storage_writable',
            'storage_size',
            'uploads_count',
            'uploads_size',
            'logs_count',
            'error_count',
            'last_cron',
            'cron_status',
            'memory_usage',
            'memory_peak',
            'disk_free',
            'disk_total',
            'extensions'
        ) );
    }

    /**
     * System logs.
     */
    public function logs( Request $request )
    {
        $level = $request->get( 'level', 'all' );
        $search = $request->get( 'search' );

        $query = SystemLog::orderBy( 'created_at', 'desc' );

        if ( $level && $level !== 'all' ) {
            $query->level( $level );
        }

        if ( $search ) {
            $query->where( 'message', 'like', "%{$search}%" );
        }

        $logs = $query->paginate( 50 );
        $levels = [
            'all' => 'All',
            SystemLog::LEVEL_DEBUG => 'Debug',
            SystemLog::LEVEL_INFO => 'Info',
            SystemLog::LEVEL_NOTICE => 'Notice',
            SystemLog::LEVEL_WARNING => 'Warning',
            SystemLog::LEVEL_ERROR => 'Error',
            SystemLog::LEVEL_CRITICAL => 'Critical',
        ];

        return view( 'admin.system.logs', compact( 'logs', 'levels', 'level', 'search' ) );
    }

    /**
     * Clear logs.
     */
    public function logsClear( Request $request )
    {
        $level = $request->get( 'level', 'all' );

        if ( $level === 'all' ) {
            SystemLog::truncate();
        } else {
            SystemLog::level( $level )->delete();
        }

        return redirect()->route( 'admin.system.logs' )->with( 'success', 'Logs cleared successfully.' );
    }

    /**
     * System tools.
     */
    public function tools()
    {
        $output = Cache::get( 'system_tools_output' );
        if ( $output ) {
            Cache::forget( 'system_tools_output' );
        }

        return view( 'admin.system.tools', compact( 'output' ) );
    }

    /**
     * Execute tool action.
     */
    public function toolsExecute( Request $request )
    {
        $outputLog = new BufferedOutput();

        switch ( $request->action ) {
            case 'clear_cache':
                Artisan::call( 'cache:clear', [], $outputLog );
                Artisan::call( 'config:clear', [], $outputLog );
                Artisan::call( 'view:clear', [], $outputLog );
                SystemLog::info( 'Cache cleared via admin tools.' );
                break;

            case 'clear_logs':
                $count = SystemLog::count();
                SystemLog::truncate();
                SystemLog::info( "Cleared {$count} system logs." );
                $outputLog->writeln( "Cleared {$count} logs." );
                break;

            case 'storage_link':
                Artisan::call( 'storage:link', [], $outputLog );
                SystemLog::info( 'Storage link created.' );
                break;

            case 'migrate':
                Artisan::call( 'migrate', [ '--force' => true ], $outputLog );
                SystemLog::info( 'Database migrations run.' );
                break;

            case 'optimize':
                Artisan::call( 'optimize', [], $outputLog );
                SystemLog::info( 'Application optimized.' );
                break;

            case 'run_cron':
                Artisan::call( 'system:cron', [], $outputLog );
                break;

            default:
                $outputLog->writeln( 'Unknown action.' );
        }

        $output = $outputLog->fetch();

        if ( $output ) {
            Cache::forever( 'system_tools_output', $output );
        }

        return redirect()->route( 'admin.system.tools' )->withInput();
    }

    /**
     * Format bytes to human readable.
     */
    private function formatBytes( $bytes, $precision = 2 ): string
    {
        $units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];

        for ( $i = 0; $bytes > 1024 && $i < count( $units ) - 1; $i++ ) {
            $bytes /= 1024;
        }

        return round( $bytes, $precision ) . ' ' . $units[ $i ];
    }

    /**
     * Get directory size.
     */
    private function getDirectorySize( $path ): string
    {
        $size = 0;

        if ( is_dir( $path ) ) {
            foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ) ) as $file ) {
                if ( $file->isFile() ) {
                    $size += $file->getSize();
                }
            }
        }

        return $this->formatBytes( $size );
    }
}
