<?php

namespace Dropzone\S3;

use Illuminate\Support\ServiceProvider;
use App\Services\StorageDriverInterface;
use Dropzone\S3\Storage\S3StorageDriver;

class DropzoneS3ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/s3.php',
            'dropzone-s3'
        );

        // Only register if enabled
        if ( config( 'dropzone-s3.enabled' ) ) {
            // Register as storage driver
            config( [
                'extensions.drivers.s3' => S3StorageDriver::class,
            ] );
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(
            __DIR__ . '/../views',
            'dropzone-s3'
        );

        // Publish config
        $this->publishes( [
            __DIR__ . '/Config/s3.php' => config_path( 'dropzone-s3.php' ),
        ], 'dropzone-s3-config' );

        // Add to admin menu if enabled
        if ( config( 'dropzone-s3.enabled' ) ) {
            // Register admin settings page
            // This would integrate with Dropzone's admin UI
        }
    }
}
