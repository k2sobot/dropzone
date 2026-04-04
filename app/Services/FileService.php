<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
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
    public function store( UploadedFile $file, ?string $uploaderIp = null, ?User $user = null ): Upload
    {
        $uuid = (string) Str::uuid();
        $filename = $file->getClientOriginalName();
        $path = "uploads/{$uuid}/{$filename}";
        $fileSize = $file->getSize();

        // Store via storage driver
        $storedPath = $this->storage->store(
            $path,
            file_get_contents( $file->getRealPath() )
        );

        // Get user or check for authenticated user
        $user = $user ?? Auth::user();

        // Get expiration setting (user-specific or global default)
        $expirationHours = $user
            ? $user->default_expiration
            : (int) AdminSetting::get( 'default_expiration', 24 );

        // Create database record
        $upload = Upload::create( [
            'id'           => $uuid,
            'user_id'      => $user?->id,
            'filename'     => $filename,
            'path'         => $storedPath,
            'size'         => $fileSize,
            'mime_type'    => $file->getMimeType(),
            'uploader_ip'  => $uploaderIp,
            'expires_at'   => now()->addHours( $expirationHours ),
        ] );

        // Update user storage usage if applicable
        if ( $user ) {
            $user->addStorageUsed( $fileSize );
        }

        return $upload;
    }

    /**
     * Check if user can upload a file of given size.
     */
    public function canUpload( int $fileSize, ?User $user = null ): array
    {
        $user = $user ?? Auth::user();

        // Get max file size (user-specific or global)
        $maxFileSize = $user
            ? $user->max_file_size
            : (int) AdminSetting::get( 'max_file_size', 104857600 );

        if ( $fileSize > $maxFileSize ) {
            return [
                'allowed' => false,
                'reason'  => 'File size exceeds maximum allowed (' . round( $maxFileSize / 1048576, 1 ) . 'MB)',
            ];
        }

        // Check user storage quota if applicable
        if ( $user && $user->hasStorageQuota() ) {
            $remaining = $user->getRemainingStorage();
            if ( $fileSize > $remaining ) {
                return [
                    'allowed' => false,
                    'reason'  => 'Insufficient storage quota. ' . round( $remaining / 1048576, 1 ) . 'MB remaining.',
                ];
            }
        }

        // Check daily upload limit if applicable
        if ( $user && $user->getAttributes()['max_uploads_per_day'] ) {
            $todayUploads = Upload::where( 'user_id', $user->id )
                ->whereDate( 'created_at', today() )
                ->count();

            if ( $todayUploads >= $user->max_uploads_per_day ) {
                return [
                    'allowed' => false,
                    'reason'  => 'Daily upload limit reached (' . $user->max_uploads_per_day . ' uploads/day)',
                ];
            }
        }

        return [ 'allowed' => true ];
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
        // Get file size before deletion
        $fileSize = $upload->size;
        $user = $upload->user;

        // Delete from storage
        if ( $this->storage->exists( $upload->path ) ) {
            $this->storage->delete( $upload->path );
        }

        // Delete from database
        $deleted = $upload->delete();

        // Update user storage usage if applicable
        if ( $deleted && $user ) {
            $user->subtractStorageUsed( $fileSize );
        }

        return $deleted;
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
