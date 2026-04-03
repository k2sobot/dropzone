<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Driver
    |--------------------------------------------------------------------------
    |
    | The default storage driver for file uploads. Extensions can register
    | additional drivers here.
    |
    */

    'default' => env( 'STORAGE_DRIVER', 'local' ),

    /*
    |--------------------------------------------------------------------------
    | Registered Storage Drivers
    |--------------------------------------------------------------------------
    |
    | Storage drivers implement StorageDriverInterface. Extensions can add
    | their drivers to this array.
    |
    */

    'drivers' => [
        'local' => \App\Services\LocalStorageDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enabled Extensions
    |--------------------------------------------------------------------------
    |
    | Extensions listed here will be loaded on startup. Extensions can also
    | be discovered automatically from the extensions/ directory.
    |
    */

    'extensions' => [
        // 'dropzone/s3' => true,
        // 'dropzone/password' => true,
    ],
];
