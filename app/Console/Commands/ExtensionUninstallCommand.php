<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExtensionUninstallCommand extends Command
{
    protected $signature = 'dropzone:uninstall-extension {package}
                            {--force : Remove without confirmation}';

    protected $description = 'Uninstall a Dropzone extension';

    public function handle(): int
    {
        $package = $this->argument( 'package' );

        // Find extension directory
        $extensionDir = $this->findExtensionDirectory( $package );

        if ( ! $extensionDir ) {
            $this->error( "Extension not found: {$package}" );

            return self::FAILURE;
        }

        // Confirm uninstall
        if ( ! $this->option( 'force' ) && ! $this->confirm( "Remove {$package}?" ) ) {
            $this->info( 'Cancelled.' );

            return self::SUCCESS;
        }

        // Run uninstall script if present
        $uninstallScript = "{$extensionDir}/uninstall.php";

        if ( file_exists( $uninstallScript ) ) {
            $this->line( 'Running uninstall script...' );
            require $uninstallScript;
        }

        // Remove directory
        File::deleteDirectory( $extensionDir );

        // Disable in config
        $this->disableExtension( $package );

        $this->info( "✓ {$package} uninstalled" );

        return self::SUCCESS;
    }

    /**
     * Find extension directory.
     */
    protected function findExtensionDirectory( string $package ): ?string
    {
        $extensionsPath = base_path( 'extensions' );

        if ( ! is_dir( $extensionsPath ) ) {
            return null;
        }

        // Check for official extension naming
        if ( str_starts_with( $package, 'dropzone/' ) ) {
            $shortName = str_replace( 'dropzone/', '', $package );
            $path = "{$extensionsPath}/dropzone-{$shortName}";

            if ( is_dir( $path ) ) {
                return $path;
            }
        }

        // Check all extension directories
        foreach ( File::directories( $extensionsPath ) as $dir ) {
            $composerPath = "{$dir}/composer.json";

            if ( file_exists( $composerPath ) ) {
                $composer = json_decode( file_get_contents( $composerPath ), true );

                if ( ( $composer['name'] ?? '' ) === $package ) {
                    return $dir;
                }
            }
        }

        return null;
    }

    /**
     * Disable extension in config.
     */
    protected function disableExtension( string $package ): void
    {
        $configPath = config_path( 'extensions.php' );

        if ( ! file_exists( $configPath ) ) {
            return;
        }

        $config = include $configPath;

        if ( isset( $config['extensions'][ $package ] ) ) {
            unset( $config['extensions'][ $package ] );

            $content = "<?php\n\nreturn " . $this->arrayToString( $config ) . ";\n";
            file_put_contents( $configPath, $content );
        }
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
}
