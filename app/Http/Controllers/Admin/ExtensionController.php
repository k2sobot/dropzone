<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ExtensionController extends Controller
{
    /**
     * Show the extensions management page.
     */
    public function index(): View
    {
        $extensions = $this->getInstalledExtensions();

        $available = [
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

        return view( 'admin.extensions.index', [
            'extensions' => $extensions,
            'available'  => $available,
        ] );
    }

    /**
     * Get list of installed extensions.
     */
    protected function getInstalledExtensions(): array
    {
        $extensionsPath = base_path( 'extensions' );

        if ( ! is_dir( $extensionsPath ) ) {
            return [];
        }

        $directories = File::directories( $extensionsPath );
        $extensions = [];

        foreach ( $directories as $dir ) {
            $name = basename( $dir );
            $composerPath = "{$dir}/composer.json";

            if ( file_exists( $composerPath ) ) {
                $composer = json_decode( file_get_contents( $composerPath ), true );
                $enabled = $this->isExtensionEnabled( $composer['name'] ?? '' );

                $extensions[] = [
                    'directory'   => $name,
                    'name'        => $composer['extra']['dropzone']['name']
                        ?? $composer['description']
                        ?? $name,
                    'description' => $composer['extra']['dropzone']['description']
                        ?? $composer['description']
                        ?? '',
                    'type'        => $composer['extra']['dropzone']['storage_driver'] ? 'Storage' : 'Feature',
                    'enabled'     => $enabled,
                    'composer_name' => $composer['name'] ?? '',
                ];
            } else {
                $extensions[] = [
                    'directory' => $name,
                    'name'      => $name,
                    'description' => 'Unknown',
                    'type'      => '-',
                    'enabled'   => false,
                    'composer_name' => '',
                ];
            }
        }

        return $extensions;
    }

    /**
     * Check if extension is enabled in config.
     */
    protected function isExtensionEnabled( string $package ): bool
    {
        $config = config( 'extensions.extensions', [] );

        return (bool) ( $config[ $package ] ?? false );
    }
}
