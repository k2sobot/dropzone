<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SetupController extends Controller
{
    /**
     * Show the setup wizard.
     */
    public function index(): View|RedirectResponse
    {
        // Already set up?
        if ( AdminSetting::get( 'setup_complete' ) ) {
            return redirect( '/' );
        }

        $step = request( 'step', 1 );

        return view( 'setup.index', [
            'step'    => $step,
            'phpVersion' => PHP_VERSION,
            'extensions' => $this->checkExtensions(),
            'permissions' => $this->checkPermissions(),
            'writable' => $this->checkWritable(),
        ] );
    }

    /**
     * Process setup step.
     */
    public function store( Request $request ): RedirectResponse
    {
        $step = (int) $request->input( 'step', 1 );

        switch ( $step ) {
            case 1:
                return $this->processRequirements( $request );
            case 2:
                return $this->processDatabase( $request );
            case 3:
                return $this->processAdmin( $request );
            case 4:
                return $this->processSettings( $request );
            default:
                return redirect( route( 'setup', [ 'step' => 1 ] ) );
        }
    }

    /**
     * Check PHP extensions.
     */
    protected function checkExtensions(): array
    {
        return [
            'pdo'      => extension_loaded( 'pdo' ),
            'pdo_sqlite' => extension_loaded( 'pdo_sqlite' ) || extension_loaded( 'pdo_mysql' ),
            'mbstring' => extension_loaded( 'mbstring' ),
            'openssl'  => extension_loaded( 'openssl' ),
            'tokenizer' => extension_loaded( 'tokenizer' ),
            'xml'      => extension_loaded( 'xml' ),
            'json'     => extension_loaded( 'json' ),
            'fileinfo' => extension_loaded( 'fileinfo' ),
            'gd'       => extension_loaded( 'gd' ),
        ];
    }

    /**
     * Check directory permissions.
     */
    protected function checkPermissions(): array
    {
        $basePath = base_path();

        return [
            'storage' => is_readable( "{$basePath}/storage" ) && is_writable( "{$basePath}/storage" ),
            'storage_framework' => is_writable( "{$basePath}/storage/framework" ),
            'storage_logs' => is_writable( "{$basePath}/storage/logs" ),
            'bootstrap_cache' => is_writable( "{$basePath}/bootstrap/cache" ),
        ];
    }

    /**
     * Check writable paths.
     */
    protected function checkWritable(): bool
    {
        return is_writable( storage_path() ) && is_writable( base_path( 'bootstrap/cache' ) );
    }

    /**
     * Process requirements check.
     */
    protected function processRequirements( Request $request ): RedirectResponse
    {
        $extensions = $this->checkExtensions();
        $failed = array_filter( $extensions, fn( $v ) => ! $v );

        if ( ! empty( $failed ) ) {
            return back()->with( 'error', 'Missing required PHP extensions.' );
        }

        return redirect( route( 'setup', [ 'step' => 2 ] ) );
    }

    /**
     * Process database setup.
     */
    protected function processDatabase( Request $request ): RedirectResponse
    {
        try {
            // Create SQLite database if needed
            if ( config( 'database.default' ) === 'sqlite' ) {
                $dbPath = database_path( 'database.sqlite' );

                if ( ! file_exists( $dbPath ) ) {
                    File::put( $dbPath, '' );
                    chmod( $dbPath, 0666 );
                }
            }

            // Run migrations
            Artisan::call( 'migrate', [ '--force' => true ] );

            return redirect( route( 'setup', [ 'step' => 3 ] ) );
        } catch ( \Exception $e ) {
            return back()->with( 'error', 'Database error: ' . $e->getMessage() );
        }
    }

    /**
     * Process admin user creation.
     */
    protected function processAdmin( Request $request ): RedirectResponse
    {
        $request->validate( [
            'admin_password' => 'required|min:8|confirmed',
        ] );

        // Store admin password hash
        AdminSetting::set( 'admin_password', bcrypt( $request->admin_password ) );
        AdminSetting::set( 'admin_password_raw', $request->admin_password );

        return redirect( route( 'setup', [ 'step' => 4 ] ) );
    }

    /**
     * Process final settings and complete setup.
     */
    protected function processSettings( Request $request ): RedirectResponse
    {
        $request->validate( [
            'site_name' => 'required|max:255',
            'site_url'  => 'required|url',
        ] );

        AdminSetting::set( 'site_name', $request->site_name );
        AdminSetting::set( 'site_url', $request->site_url );

        // Generate app key if missing
        if ( empty( env( 'APP_KEY' ) ) ) {
            Artisan::call( 'key:generate', [ '--force' => true ] );
        }

        // Create storage link
        Artisan::call( 'storage:link' );

        // Mark setup complete
        AdminSetting::set( 'setup_complete', true );
        AdminSetting::set( 'setup_date', now()->toIso8601String() );

        return redirect( '/' )->with( 'success', 'Setup complete! Your Dropzone is ready.' );
    }
}
