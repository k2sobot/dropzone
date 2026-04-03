<?php

namespace App\Services;

use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileService
{
    protected StorageDriverInterface $storage;

    public function __construct( StorageDriverInterface $storage )
    {
        $this->storage = $storage;
    }

    /**
     * Store an uploaded file.
     */
    public function store( UploadedFile $file, ?string $uploaderIp = null ): Upload
    {
        $uuid = (string) Str::uuid();
        $filename = $file->getClientOriginalName();
        $path = "uploads/{$uuid}/{$filename}";

        // Store via storage driver
        $storedPath = $this->storage->store(
            $path,
            file_get_contents( $file->getRealPath() )
        );

        // Create database record
        return Upload::create( [
            'id'           => $uuid,
            'filename'     => $filename,
            'path'         => $storedPath,
            'size'         => $file->getSize(),
            'mime_type'    => $file->getMimeType(),
            'uploader_ip'  => $uploaderIp,
            'expires_at'   => now()->addDays( 7 ),
        ] );
    }

    /**
     * Download a file and mark as downloaded.
     */
    public function download( string $uuid ): ?array
    {
        $upload = Upload::find( $uuid );

        if ( ! $upload || ! $upload->isAvailable() ) {
            return null;
        }

        // Check for temporary URL (cloud storage)
        $tempUrl = $this->storage->temporaryUrl( $upload->path );

        if ( $tempUrl ) {
            // Redirect to cloud storage URL
            $upload->markAsDownloaded();

            return [
                'upload' => $upload,
                'url'    => $tempUrl,
            ];
        }

        // Get contents for local storage
        $contents = $this->storage->get( $upload->path );

        if ( ! $contents ) {
            return null;
        }

        // Mark as downloaded
        $upload->markAsDownloaded();

        return [
            'upload'   => $upload,
            'content'  => $contents,
        ];
    }

    /**
     * Delete a file and its record.
     */
    public function delete( Upload $upload ): bool
    {
        // Delete from storage
        if ( $this->storage->exists( $upload->path ) ) {
            $this->storage->delete( $upload->path );
        }

        // Delete from database
        return $upload->delete();
    }

    /**
     * Clean up expired uploads.
     */
    public function cleanupExpired(): int
    {
        $deleted = 0;

        $expired = Upload::where( 'expires_at', '<', now() )
            ->whereNull( 'downloaded_at' )
            ->get();

        foreach ( $expired as $upload ) {
            $this->delete( $upload );
            $deleted++;
        }

        // Force delete soft-deleted records older than 24 hours
        Upload::onlyTrashed()
            ->where( 'deleted_at', '<', now()->subDay() )
            ->forceDelete();

        return $deleted;
    }

    /**
     * Get file statistics.
     */
    public function getStats(): array
    {
        return [
            'total_files'      => Upload::count(),
            'total_size'       => Upload::sum( 'size' ),
            'active_files'     => Upload::whereNull( 'downloaded_at' )
                ->where( 'expires_at', '>', now() )
                ->count(),
            'downloaded_files' => Upload::whereNotNull( 'downloaded_at' )->count(),
            'expired_files'    => Upload::where( 'expires_at', '<', now() )
                ->whereNull( 'downloaded_at' )
                ->count(),
        ];
    }

    /**
     * Get the current storage driver.
     */
    public function getStorageDriver(): StorageDriverInterface
    {
        return $this->storage;
    }
}
