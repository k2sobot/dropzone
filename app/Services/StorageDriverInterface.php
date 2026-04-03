<?php

namespace App\Services;

interface StorageDriverInterface
{
    /**
     * Store a file and return the storage path.
     */
    public function store( string $path, $contents, array $options = [] ): string;

    /**
     * Retrieve a file's contents.
     */
    public function get( string $path ): ?string;

    /**
     * Delete a file.
     */
    public function delete( string $path ): bool;

    /**
     * Check if a file exists.
     */
    public function exists( string $path ): bool;

    /**
     * Get a temporary download URL (for cloud storage).
     * Returns null for local storage.
     */
    public function temporaryUrl( string $path, int $expires = 3600 ): ?string;

    /**
     * Get the driver name for display.
     */
    public function getName(): string;

    /**
     * Get driver-specific configuration fields.
     */
    public function getConfigFields(): array;
}
