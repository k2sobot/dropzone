<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExtensionUpdateCommand extends Command
{
    protected $signature = 'dropzone:update-extension {package?}
                            {--all : Update all extensions}
                            {--branch= : Git branch to pull from}';

    protected $description = 'Update Dropzone extensions';

    public function handle(): int
    {
        if ( $this->option( 'all' ) ) {
            return $this->updateAll();
        }

        $package = $this->argument( 'package' );

        if ( ! $package ) {
            $this->error( 'Package name required or use --all' );

            return self::FAILURE;
        }

        return $this->updateSingle( $package );
    }

    /**
     * Update all extensions.
     */
    protected function updateAll(): int
    {
        $extensionsPath = base_path( 'extensions' );

        if ( ! is_dir( $extensionsPath ) ) {
            $this->info( 'No extensions installed.' );

            return self::SUCCESS;
        }

        $extensions = File::directories( $extensionsPath );

        if ( empty( $extensions ) ) {
            $this->info( 'No extensions installed.' );

            return self::SUCCESS;
        }

        $success = true;

        foreach ( $extensions as $path ) {
            $name = basename( $path );
            $result = $this->updateExtension( $name, $path );

            if ( ! $result ) {
                $success = false;
            }
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Update a single extension.
     */
    protected function updateSingle( string $package ): int
    {
        $extensionsPath = base_path( 'extensions' );

        // Find extension directory
        $extensionDir = null;

        if ( str_starts_with( $package, 'dropzone/' ) ) {
            $shortName = str_replace( 'dropzone/', '', $package );
            $path = "{$extensionsPath}/dropzone-{$shortName}";

            if ( is_dir( $path ) ) {
                $extensionDir = $path;
            }
        }

        // Try to find by scanning
        if ( ! $extensionDir ) {
            foreach ( File::directories( $extensionsPath ) as $dir ) {
                $composerPath = "{$dir}/composer.json";

                if ( file_exists( $composerPath ) ) {
                    $composer = json_decode( file_get_contents( $composerPath ), true );

                    if ( ( $composer['name'] ?? '' ) === $package ) {
                        $extensionDir = $dir;
                        break;
                    }
                }
            }
        }

        if ( ! $extensionDir ) {
            $this->error( "Extension not found: {$package}" );

            return self::FAILURE;
        }

        $name = basename( $extensionDir );

        if ( $this->updateExtension( $name, $extensionDir ) ) {
            $this->info( "✓ {$package} updated successfully" );

            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    /**
     * Update an extension directory.
     */
    protected function updateExtension( string $name, string $path ): bool
    {
        $this->info( "Updating {$name}..." );

        // Check if it's a Git repository
        if ( ! is_dir( "{$path}/.git" ) ) {
            $this->warn( "{$name} is not a Git repository (manual install)" );

            return false;
        }

        // Run git pull
        $branch = $this->option( 'branch' ) ?? 'main';
        $process = new \Symfony\Component\Process\Process( [
            'git', 'pull', 'origin', $branch,
        ], $path );

        $process->setTimeout( 300 );

        $result = $process->run( function ( $type, $buffer ) {
            $this->output->write( $buffer );
        } );

        if ( $result !== 0 ) {
            $this->error( "Failed to update {$name}" );

            return false;
        }

        // Run composer install if needed
        if ( file_exists( "{$path}/composer.json" ) ) {
            $process = new \Symfony\Component\Process\Process( [
                'composer', 'install', '--no-dev',
            ], $path );

            $process->setTimeout( 300 );

            $process->run( function ( $type, $buffer ) {
                $this->output->write( $buffer );
            } );
        }

        // Run update script if present
        $updateScript = "{$path}/update.php";

        if ( file_exists( $updateScript ) ) {
            $this->line( 'Running update script...' );
            require $updateScript;
        }

        return true;
    }
}