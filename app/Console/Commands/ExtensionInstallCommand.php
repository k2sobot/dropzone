<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class ExtensionInstallCommand extends Command
{
    protected $signature = 'dropzone:install-extension {package}
                            {--version= : Specific version to install}
                            {--branch= : Git branch to use (for dev installs)}';

    protected $description = 'Install a Dropzone extension from GitHub or Packagist';

    protected string $extensionsPath;

    public function handle(): int
    {
        $this->extensionsPath = base_path( 'extensions' );

        if ( ! is_dir( $this->extensionsPath ) ) {
            mkdir( $this->extensionsPath, 0755, true );
        }

        $package = $this->argument( 'package' );

        // Parse package format
        if ( str_contains( $package, '/' ) ) {
            // Format: vendor/package or dropzone/s3
            if ( str_starts_with( $package, 'dropzone/' ) ) {
                // Official Dropzone extension
                return $this->installFromGitHub( $package );
            }

            // Try Packagist first, then GitHub
            return $this->installFromPackagist( $package );
        }

        // Could be a GitHub URL or shorthand
        if ( str_contains( $package, 'github.com' ) ) {
            return $this->installFromGitHubUrl( $package );
        }

        $this->error( "Unknown package format: {$package}" );
        $this->line( 'Examples:' );
        $this->line( '  php artisan dropzone:install-extension dropzone/s3' );
        $this->line( '  php artisan dropzone:install-extension dropzone/password-protection' );
        $this->line( '  php artisan dropzone:install-extension https://github.com/user/dropzone-custom' );

        return self::FAILURE;
    }

    /**
     * Install from Packagist (Composer).
     */
    protected function installFromPackagist( string $package ): int
    {
        $this->info( "Installing {$package} from Packagist..." );

        // Check if package exists
        $response = Http::get( "https://repo.packagist.org/p2/{$package}.json" );

        if ( ! $response->ok() ) {
            $this->error( "Package not found on Packagist: {$package}" );

            return self::FAILURE;
        }

        // Install via Composer
        $command = [ 'composer', 'require', $package ];

        if ( $version = $this->option( 'version' ) ) {
            $command[] = "{$package}:{$version}";
        }

        $result = $this->execCommand( $command );

        if ( $result === 0 ) {
            $this->info( "✓ {$package} installed successfully" );
            $this->line( '' );
            $this->line( 'Enable it in config/extensions.php:' );
            $this->line( "  'extensions' => ['{$package}' => true]," );

            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    /**
     * Install official Dropzone extension from GitHub.
     */
    protected function installFromGitHub( string $package ): int
    {
        $shortName = str_replace( 'dropzone/', '', $package );
        $repo = "dropzone-{$shortName}";
        $gitUrl = "https://github.com/k2sobot/{$repo}.git";

        $this->info( "Installing {$package} from GitHub..." );

        $targetPath = "{$this->extensionsPath}/{$repo}";

        if ( is_dir( $targetPath ) ) {
            $this->warn( "Extension already installed at extensions/{$repo}" );
            $this->line( 'Run `php artisan dropzone:update-extension ' . $package . '` to update' );

            return self::SUCCESS;
        }

        // Clone the repository
        $branch = $this->option( 'branch' ) ?? 'main';
        $result = $this->execCommand( [
            'git', 'clone',
            '--branch', $branch,
            '--depth', '1',
            $gitUrl,
            $targetPath,
        ] );

        if ( $result !== 0 ) {
            $this->error( "Failed to clone repository: {$gitUrl}" );

            return self::FAILURE;
        }

        // Run extension installer if present
        $this->runExtensionInstaller( $targetPath );

        // Add to extensions config
        $this->enableExtension( $package, $repo );

        $this->info( "✓ {$package} installed successfully" );

        return self::SUCCESS;
    }

    /**
     * Install from GitHub URL.
     */
    protected function installFromGitHubUrl( string $url ): int
    {
        // Parse URL to get repo info
        preg_match( '#github\.com/([^/]+)/([^/]+)#', $url, $matches );

        if ( count( $matches ) < 3 ) {
            $this->error( "Invalid GitHub URL: {$url}" );

            return self::FAILURE;
        }

        $vendor = $matches[1];
        $repo = $matches[2];
        $package = "{$vendor}/{$repo}";

        $this->info( "Installing {$package} from GitHub..." );

        $targetPath = "{$this->extensionsPath}/{$repo}";

        if ( is_dir( $targetPath ) ) {
            $this->warn( "Extension already installed" );

            return self::SUCCESS;
        }

        $branch = $this->option( 'branch' ) ?? 'main';
        $result = $this->execCommand( [
            'git', 'clone',
            '--branch', $branch,
            '--depth', '1',
            "{$url}.git",
            $targetPath,
        ] );

        if ( $result !== 0 ) {
            $this->error( "Failed to clone repository" );

            return self::FAILURE;
        }

        $this->runExtensionInstaller( $targetPath );
        $this->enableExtension( $package, $repo );

        $this->info( "✓ Extension installed successfully" );

        return self::SUCCESS;
    }

    /**
     * Run extension's install.php if present.
     */
    protected function runExtensionInstaller( string $path ): void
    {
        $installScript = "{$path}/install.php";

        if ( file_exists( $installScript ) ) {
            $this->line( 'Running extension installer...' );
            require $installScript;
        }

        // Run composer install if extension has dependencies
        if ( file_exists( "{$path}/composer.json" ) ) {
            $this->line( 'Installing extension dependencies...' );
            $this->execCommand( [ 'composer', 'install', '--no-dev' ], $path );
        }
    }

    /**
     * Enable extension in config.
     */
    protected function enableExtension( string $package, string $directory ): void
    {
        $configPath = config_path( 'extensions.php' );

        if ( ! file_exists( $configPath ) ) {
            return;
        }

        $config = include $configPath;

        if ( ! isset( $config['extensions'] ) ) {
            $config['extensions'] = [];
        }

        $config['extensions'][ $package ] = true;

        $content = "<?php\n\nreturn " . $this->arrayToString( $config ) . ";\n";
        file_put_contents( $configPath, $content );
    }

    /**
     * Convert array to PHP config string.
     */
    protected function arrayToString( array $array, int $indent = 0 ): string
    {
        $spaces = str_repeat( '    ', $indent );
        $lines = [ '[' ];

        foreach ( $array as $key => $value ) {
            $keyStr = is_string( $key ) ? "'{$key}' => " : '';
            $valueStr = is_array( $value )
                ? $this->arrayToString( $value, $indent + 1 )
                : var_export( $value, true );
            $lines[] = "{$spaces}    {$keyStr}{$valueStr},";
        }

        $lines[] = "{$spaces}]";

        return implode( "\n", $lines );
    }

    /**
     * Execute a shell command.
     */
    protected function execCommand( array $command, ?string $cwd = null ): int
    {
        $process = new \Symfony\Component\Process\Process( $command, $cwd ?? base_path() );
        $process->setTimeout( 300 );

        return $process->run( function ( $type, $buffer ) {
            $this->output->write( $buffer );
        } );
    }
}
