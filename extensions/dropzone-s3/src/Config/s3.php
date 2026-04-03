<?php

return [
    /*
    |--------------------------------------------------------------------------
    | S3 / DO Spaces Extension
    |--------------------------------------------------------------------------
    */

    'enabled' => env( 'S3_ENABLED', false ),

    'key'     => env( 'S3_KEY', '' ),
    'secret'  => env( 'S3_SECRET', '' ),
    'bucket'  => env( 'S3_BUCKET', '' ),
    'region'  => env( 'S3_REGION', 'us-east-1' ),

    // Set true for DigitalOcean Spaces
    'do_spaces' => env( 'S3_DO_SPACES', false ),
    'endpoint'  => env( 'S3_ENDPOINT', '' ),
];
