# Dropzone Extension System

## Overview

Dropzone supports optional extensions that can be installed via Composer or manually. Each extension can add:

- Storage drivers (S3, DO Spaces, Backblaze, etc.)
- Features (password-protected links, custom expiration, email notifications)
- Admin UI panels
- Event listeners

## Extension Structure

```
extensions/
└── dropzone-s3/
    ├── composer.json          # Extension metadata
    ├── src/
    │   ├── DropzoneS3ServiceProvider.php
    │   ├── Storage/
    │   │   └── S3StorageDriver.php
    │   └── Config/
    │       └── s3.php
    ├── views/
    │   └── admin/
    │       └── settings-s3.blade.php
    └── README.md
```

## Creating an Extension

### 1. composer.json

```json
{
    "name": "dropzone/s3",
    "type": "dropzone-extension",
    "description": "S3/DO Spaces storage for Dropzone",
    "require": {
        "php": "^8.2",
        "aws/aws-sdk-php": "^3.0"
    },
    "extra": {
        "dropzone": {
            "name": "S3 Storage",
            "description": "Store files on Amazon S3 or DigitalOcean Spaces",
            "icon": "cloud",
            "settings_view": "dropzone-s3::admin.settings",
            "storage_driver": "s3"
        }
    },
    "autoload": {
        "psr-4": {
            "Dropzone\\S3\\": "src/"
        }
    }
}
```

### 2. Service Provider

```php
<?php

namespace Dropzone\S3;

use Illuminate\Support\ServiceProvider;
use App\Services\StorageDriverInterface;
use Dropzone\S3\Storage\S3StorageDriver;

class DropzoneS3ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register config
        $this->mergeConfigFrom(
            __DIR__.'/Config/s3.php', 'dropzone-s3'
        );

        // Register storage driver if enabled
        if (config('dropzone-s3.enabled')) {
            $this->app->bind(StorageDriverInterface::class, S3StorageDriver::class);
        }
    }

    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../views', 'dropzone-s3');

        // Publish config
        $this->publishes([
            __DIR__.'/Config/s3.php' => config_path('dropzone-s3.php'),
        ], 'dropzone-s3-config');
    }
}
```

### 3. Storage Driver Interface

Extensions must implement this interface:

```php
<?php

namespace App\Services;

interface StorageDriverInterface
{
    /**
     * Store a file and return the path.
     */
    public function store(string $path, $contents, array $options = []): string;

    /**
     * Retrieve a file's contents.
     */
    public function get(string $path): ?string;

    /**
     * Delete a file.
     */
    public function delete(string $path): bool;

    /**
     * Check if a file exists.
     */
    public function exists(string $path): bool;

    /**
     * Get a temporary download URL.
     */
    public function temporaryUrl(string $path, int $expires = 3600): string;

    /**
     * Get the driver name.
     */
    public function getName(): string;
}
```

## Official Extensions

| Extension | Description | Status |
|-----------|-------------|--------|
| `dropzone/s3` | Amazon S3 + DO Spaces | Planned |
| `dropzone/backblaze` | Backblaze B2 | Planned |
| `dropzone/password` | Password-protected links | Planned |
| `dropzone/expiration` | Custom expiration times | Planned |
| `dropzone/email` | Email notifications | Planned |
| `dropzone/analytics` | Download analytics | Planned |

## Installing Extensions

### Via Composer

```bash
composer require dropzone/s3
php artisan dropzone:install-extension dropzone/s3
```

### Manual Install

1. Extract to `extensions/dropzone-s3/`
2. Add to `config/extensions.php`:
```php
'extensions' => [
    'dropzone/s3' => [
        'enabled' => true,
        'path' => base_path('extensions/dropzone-s3'),
    ],
],
```

## Extension Discovery

Dropzone auto-discovers extensions on startup:

```php
// config/extensions.php
return [
    'extensions' => [], // Auto-populated
    'storage_drivers' => [
        'local' => \App\Services\LocalStorageDriver::class,
    ],
];
```

## Admin UI Integration

Extensions can register admin menu items and settings pages:

```php
// In ServiceProvider::boot()
app('dropzone')->registerAdminMenu([
    's3' => [
        'label' => 'S3 Settings',
        'icon' => 'cloud',
        'view' => 'dropzone-s3::admin.settings',
    ],
]);
```

## Events

Extensions can hook into Dropzone events:

- `FileUploaded` - After file upload
- `FileDownloaded` - After file download
- `FileDeleted` - After file deletion
- `FileExpired` - After file expires
- `AdminSettingsSaved` - After settings update

```php
use App\Events\FileUploaded;

Event::listen(FileUploaded::class, function ($event) {
    // Log to external service
    Log::channel('s3')->info('File uploaded', [
        'uuid' => $event->upload->id,
        'size' => $event->upload->size,
    ]);
});
```
