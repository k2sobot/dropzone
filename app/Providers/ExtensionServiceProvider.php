<?php

namespace App\Providers;

use App\Services\FileService;
use App\Services\StorageDriverInterface;
use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the default storage driver
        $this->app->bind( StorageDriverInterface::class, function ( $app ) {
            $driver = config( 'extensions.default', 'local' );
            $drivers = config( 'extensions.drivers', [] );

            if ( ! isset( $drivers[ $driver ] ) ) {
                $driver = 'local';
            }

            return $app->make( $drivers[ $driver ] );
        } );

        // Load extensions from config
        $extensions = config( 'extensions.extensions', [] );

        foreach ( $extensions as $extension => $enabled ) {
            if ( $enabled ) {
                $this->loadExtension( $extension );
            }
        }

        // Auto-discover extensions from extensions/ directory
        $this->discoverExtensions();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Extensions can register event listeners here
    }

    /**
     * Load an extension by name.
     */
    protected function loadExtension( string $extension ): void
    {
        $path = base_path( "extensions/{$extension}" );

        if ( ! is_dir( $path ) ) {
            return;
        }

        // Load extension composer.json for metadata
        $composerPath = "{$path}/composer.json";

        if ( file_exists( $composerPath ) ) {
            $composer = json_decode( file_get_contents( $composerPath ), true );

            // Register autoloading if defined
            if ( isset( $composer['autoload']['psr-4'] ) ) {
                foreach ( $composer['autoload']['psr-4'] as $namespace => $dir ) {
                    $this->app->bind( $namespace, function () use ( $path, $dir ) {
                        return require "{$path}/{$dir}";
                    } );
                }
            }

            // Load service provider if defined
            if ( isset( $composer['extra']['dropzone']['provider'] ) ) {
                $provider = $composer['extra']['dropzone']['provider'];
                $this->app->register( $provider );
            }

            // Register storage driver if defined
            if ( isset( $composer['extra']['dropzone']['storage_driver'] ) ) {
                $driverName = $composer['extra']['dropzone']['storage_driver'];
                $driverClass = $composer['extra']['dropzone']['driver_class'] ?? null;

                if ( $driverClass ) {
                    config( [ "extensions.drivers.{$driverName}" => $driverClass ] );
                }
            }
        }
    }

    /**
     * Auto-discover extensions from the extensions/ directory.
     */
    protected function discoverExtensions(): void
    {
        $extensionsPath = base_path( 'extensions' );

        if ( ! is_dir( $extensionsPath ) ) {
            return;
        }

        $directories = scandir( $extensionsPath );

        foreach ( $directories as $dir ) {
            if ( $dir === '.' || $dir === '..' ) {
                continue;
            }

            $extensionPath = "{$extensionsPath}/{$dir}";

            if ( is_dir( $extensionPath ) && file_exists( "{$extensionPath}/composer.json" ) ) {
                // Check if enabled in config
                $enabled = config( "extensions.extensions.{$dir}", false );

                if ( $enabled ) {
                    $this->loadExtension( $dir );
                }
            }
        }
    }
}
