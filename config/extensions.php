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
    /*
    |--------------------------------------------------------------------------
    | Official modules (FreeScout-style)
    |--------------------------------------------------------------------------
    |
    | Core is free. Official modules are one-time purchases with 1 year of
    | updates included. License keys are issued with:
    |   php artisan dropzone:license-issue dropzone/s3 --domain=example.com
    |
    */

    'official' => [
        'dropzone/s3' => [
            'name' => 'S3 / DO Spaces Storage',
            'description' => 'Store files on Amazon S3 or DigitalOcean Spaces',
            'repo' => 'dropzone-s3',
            'paid' => true,
            'price' => 29,
            'currency' => 'USD',
            'billing' => 'one-time',
            'updates' => '1 year of updates included',
        ],
        'dropzone/password-protection' => [
            'name' => 'Password Protection',
            'description' => 'Password-protect download links',
            'repo' => 'dropzone-password-protection',
            'paid' => true,
            'price' => 19,
            'currency' => 'USD',
            'billing' => 'one-time',
            'updates' => '1 year of updates included',
        ],
        'dropzone/custom-expiration' => [
            'name' => 'Custom Expiration',
            'description' => 'Per-upload expiration times',
            'repo' => 'dropzone-custom-expiration',
            'paid' => true,
            'price' => 15,
            'currency' => 'USD',
            'billing' => 'one-time',
            'updates' => '1 year of updates included',
        ],
        'dropzone/email-notifications' => [
            'name' => 'Email Notifications',
            'description' => 'Email when a file is downloaded',
            'repo' => 'dropzone-email-notifications',
            'paid' => true,
            'price' => 19,
            'currency' => 'USD',
            'billing' => 'one-time',
            'updates' => '1 year of updates included',
        ],
        'dropzone/analytics' => [
            'name' => 'Analytics',
            'description' => 'Download stats and reports',
            'repo' => 'dropzone-analytics',
            'paid' => true,
            'price' => 29,
            'currency' => 'USD',
            'billing' => 'one-time',
            'updates' => '1 year of updates included',
        ],
    ],
];
