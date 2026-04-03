<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Store an uploaded file.
     */
    public function store(UploadedFile $file, ?string $uploaderIp = null): Upload
    {
        $uuid = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension();
        $filename = $file->getClientOriginalName();
        $path = "uploads/{$uuid}/{$filename}";

        // Store the file
        Storage::disk('local')->putFileAs(
            "uploads/{$uuid}",
            $file,
            $filename
        );

        // Create database record
        return Upload::create([
            'id' => $uuid,
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploader_ip' => $uploaderIp,
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Download a file and mark as downloaded.
     */
    public function download(string $uuid): ?array
    {
        $upload = Upload::find($uuid);

        if (! $upload || ! $upload->isAvailable()) {
            return null;
        }

        if (! Storage::disk('local')->exists($upload->path)) {
            return null;
        }

        // Mark as downloaded
        $upload->markAsDownloaded();

        return [
            'upload' => $upload,
            'content' => Storage::disk('local')->get($upload->path),
        ];
    }

    /**
     * Delete a file and its record.
     */
    public function delete(Upload $upload): bool
    {
        // Delete from storage
        if (Storage::disk('local')->exists($upload->path)) {
            Storage::disk('local')->delete($upload->path);
            // Try to remove the parent directory
            $dir = dirname($upload->path);
            Storage::disk('local')->deleteDirectory($dir);
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

        $expired = Upload::where('expires_at', '<', now())
            ->whereNull('downloaded_at')
            ->get();

        foreach ($expired as $upload) {
            $this->delete($upload);
            $deleted++;
        }

        // Also permanently delete soft-deleted records older than 24 hours
        Upload::onlyTrashed()
            ->where('deleted_at', '<', now()->subDay())
            ->forceDelete();

        return $deleted;
    }

    /**
     * Get file statistics.
     */
    public function getStats(): array
    {
        return [
            'total_files' => Upload::count(),
            'total_size' => Upload::sum('size'),
            'active_files' => Upload::whereNull('downloaded_at')
                ->where('expires_at', '>', now())
                ->count(),
            'downloaded_files' => Upload::whereNotNull('downloaded_at')->count(),
            'expired_files' => Upload::where('expires_at', '<', now())
                ->whereNull('downloaded_at')
                ->count(),
        ];
    }
}
