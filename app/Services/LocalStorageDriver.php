<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class LocalStorageDriver implements StorageDriverInterface
{
    /**
     * Store a file and return the path.
     */
    public function store( string $path, $contents, array $options = [] ): string
    {
        Storage::disk( 'local' )->put( $path, $contents );

        return $path;
    }

    /**
     * Retrieve a file's contents.
     */
    public function get( string $path ): ?string
    {
        if ( ! $this->exists( $path ) ) {
            return null;
        }

        return Storage::disk( 'local' )->get( $path );
    }

    /**
     * Delete a file.
     */
    public function delete( string $path ): bool
    {
        if ( ! $this->exists( $path ) ) {
            return false;
        }

        return Storage::disk( 'local' )->delete( $path );
    }

    /**
     * Check if a file exists.
     */
    public function exists( string $path ): bool
    {
        return Storage::disk( 'local' )->exists( $path );
    }

    /**
     * Local storage doesn't support temporary URLs.
     */
    public function temporaryUrl( string $path, int $expires = 3600 ): ?string
    {
        return null;
    }

    /**
     * Get the driver name.
     */
    public function getName(): string
    {
        return 'Local Storage';
    }

    /**
     * Get configuration fields for admin UI.
     */
    public function getConfigFields(): array
    {
        return [];
    }
}
