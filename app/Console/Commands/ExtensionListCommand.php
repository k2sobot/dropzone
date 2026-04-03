<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExtensionListCommand extends Command
{
    protected $signature = 'dropzone:extensions
                            {--installed : Show only installed extensions}
                            {--available : Show available official extensions}';

    protected $description = 'List Dropzone extensions';

    protected array $officialExtensions = [
        'dropzone/s3' => [
            'name'        => 'S3 / DO Spaces Storage',
            'description' => 'Store files on Amazon S3 or DigitalOcean Spaces',
            'repo'        => 'dropzone-s3',
        ],
        'dropzone/password-protection' => [
            'name'        => 'Password Protection',
            'description' => 'Add password protection to download links',
            'repo'        => 'dropzone-password-protection',
        ],
        'dropzone/custom-expiration' => [
            'name'        => 'Custom Expiration',
            'description' => 'Allow custom expiration times per upload',
            'repo'        => 'dropzone-custom-expiration',
        ],
        'dropzone/email-notifications' => [
            'name'        => 'Email Notifications',
            'description' => 'Send email notifications when files are downloaded',
            'repo'        => 'dropzone-email-notifications',
        ],
        'dropzone/analytics' => [
            'name'        => 'Analytics',
            'description' => 'Track download statistics and generate reports',
            'repo'        => 'dropzone-analytics',
        ],
    ];

    public function handle(): int
    {
        if ( $this->option( 'available' ) ) {
            return $this->listAvailable();
        }

        return $this->listInstalled();
    }

    /**
     * List installed extensions.
     */
    protected function listInstalled(): int
    {
        $extensionsPath = base_path( 'extensions' );

        if ( ! is_dir( $extensionsPath ) ) {
            $this->info( 'No extensions installed.' );
            $this->line( '' );
            $this->line( 'Install an extension with:' );
            $this->line( '  php artisan dropzone:install-extension dropzone/s3' );
            $this->line( '' );
            $this->line( 'See available extensions with:' );
            $this->line( '  php artisan dropzone:extensions --available' );

            return self::SUCCESS;
        }

        $extensions = File::directories( $extensionsPath );

        if ( empty( $extensions ) ) {
            $this->info( 'No extensions installed.' );

            return self::SUCCESS;
        }

        $this->info( 'Installed Extensions:' );
        $this->line( '' );

        $rows = [];

        foreach ( $extensions as $path ) {
            $name = basename( $path );
            $composerPath = "{$path}/composer.json";

            if ( file_exists( $composerPath ) ) {
                $composer = json_decode( file_get_contents( $composerPath ), true );
                $rows[] = [
                    $name,
                    $composer['extra']['dropzone']['name'] ?? $composer['description'] ?? '',
                    $composer['extra']['dropzone']['storage_driver'] ? 'Storage' : 'Feature',
                    $this->getExtensionStatus( $name ),
                ];
            } else {
                $rows[] = [
                    $name,
                    'Unknown',
                    '-',
                    $this->getExtensionStatus( $name ),
                ];
            }
        }

        $this->table(
            [ 'Extension', 'Description', 'Type', 'Status' ],
            $rows
        );

        return self::SUCCESS;
    }

    /**
     * List available official extensions.
     */
    protected function listAvailable(): int
    {
        $this->info( 'Available Official Extensions:' );
        $this->line( '' );

        $rows = [];
        $installed = $this->getInstalledExtensions();

        foreach ( $this->officialExtensions as $package => $info ) {
            $isInstalled = in_array( $info['repo'], $installed );

            $rows[] = [
                $package,
                $info['name'],
                $info['description'],
                $isInstalled ? '✓ Installed' : 'Available',
            ];
        }

        $this->table(
            [ 'Package', 'Name', 'Description', 'Status' ],
            $rows
        );

        $this->line( '' );
        $this->line( 'Install with:' );
        $this->line( '  php artisan dropzone:install-extension dropzone/s3' );

        return self::SUCCESS;
    }

    /**
     * Get list of installed extension directories.
     */
    protected function getInstalledExtensions(): array
    {
        $extensionsPath = base_path( 'extensions' );

        if ( ! is_dir( $extensionsPath ) ) {
            return [];
        }

        return array_map( 'basename', File::directories( $extensionsPath ) );
    }

    /**
     * Get extension status.
     */
    protected function getExtensionStatus( string $name ): string
    {
        $config = config( 'extensions.extensions', [] );

        foreach ( $config as $package => $enabled ) {
            if ( str_contains( $package, $name ) && $enabled ) {
                return '✓ Enabled';
            }
        }

        return 'Disabled';
    }
}
