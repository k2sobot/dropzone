<?php

namespace App\Providers;

use App\Services\FileService;
use App\Services\StorageDriverInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the storage driver based on config
        $this->app->bind( StorageDriverInterface::class, function ( $app ) {
            $driver = config( 'extensions.default', 'local' );
            $drivers = config( 'extensions.drivers', [] );

            if ( ! isset( $drivers[ $driver ] ) ) {
                $driver = 'local';
            }

            return $app->make( $drivers[ $driver ] );
        } );

        // Bind FileService with storage driver injected
        $this->app->bind( FileService::class, function ( $app ) {
            return new FileService(
                $app->make( StorageDriverInterface::class )
            );
        } );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
