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

    public function __construct(StorageDriverInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Store an uploaded file.
     */
    public function store(UploadedFile $file, ?string $uploaderIp = null, ?User $user = null): Upload
    {
        $uuid = (string) Str::uuid();
        $filename = $this->safeFilename($file->getClientOriginalName());
        $path = "uploads/{$uuid}/{$filename}";
        $fileSize = $file->getSize();

        $stream = fopen($file->getRealPath(), 'r');
        if ($stream === false) {
            throw new \RuntimeException('Unable to read uploaded file.');
        }

        try {
            $storedPath = $this->storage->store($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $user = $user ?? Auth::user();

        $expirationHours = $user
            ? $user->default_expiration
            : (int) AdminSetting::get('default_expiration', 24);

        $upload = Upload::create([
            'id' => $uuid,
            'user_id' => $user?->id,
            'filename' => $filename,
            'path' => $storedPath,
            'size' => $fileSize,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'uploader_ip' => $uploaderIp,
            'expires_at' => now()->addHours($expirationHours),
        ]);

        if ($user) {
            $user->addStorageUsed($fileSize);
        }

        return $upload;
    }

    /**
     * Check if user can upload a file of given size.
     */
    public function canUpload(int $fileSize, ?User $user = null): array
    {
        $user = $user ?? Auth::user();

        $maxFileSize = $user
            ? $user->max_file_size
            : (int) AdminSetting::get('max_file_size', 104857600);

        if ($fileSize > $maxFileSize) {
            return [
                'allowed' => false,
                'reason' => 'File size exceeds maximum allowed ('.round($maxFileSize / 1048576, 1).'MB)',
            ];
        }

        if ($user && $user->hasStorageQuota()) {
            $remaining = $user->getRemainingStorage();
            if ($fileSize > $remaining) {
                return [
                    'allowed' => false,
                    'reason' => 'Insufficient storage quota. '.round($remaining / 1048576, 1).'MB remaining.',
                ];
            }
        }

        $dailyLimit = $user?->getRawOriginal('max_uploads_per_day');
        if ($user && $dailyLimit) {
            $todayUploads = Upload::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count();

            if ($todayUploads >= (int) $dailyLimit) {
                return [
                    'allowed' => false,
                    'reason' => 'Daily upload limit reached ('.$dailyLimit.' uploads/day)',
                ];
            }
        }

        return ['allowed' => true];
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

        $claimed = Upload::where('id', $uuid)
            ->whereNull('downloaded_at')
            ->where('expires_at', '>', now())
            ->update(['downloaded_at' => now()]);

        if ($claimed === 0) {
            return null;
        }

        $upload->refresh();

        $tempUrl = $this->storage->temporaryUrl($upload->path);

        if ($tempUrl) {
            return [
                'upload' => $upload,
                'url' => $tempUrl,
            ];
        }

        $contents = $this->storage->get($upload->path);

        if (! $contents) {
            return null;
        }

        $this->storage->delete($upload->path);

        if ($upload->user) {
            $upload->user->subtractStorageUsed($upload->size);
        }

        return [
            'upload' => $upload,
            'content' => $contents,
        ];
    }

    /**
     * Delete a file and its record.
     */
    public function delete(Upload $upload): bool
    {
        $fileSize = $upload->size;
        $user = $upload->user;

        if ($this->storage->exists($upload->path)) {
            $this->storage->delete($upload->path);
        }

        $deleted = $upload->delete();

        if ($deleted && $user) {
            $user->subtractStorageUsed($fileSize);
        }

        return (bool) $deleted;
    }

    /**
     * Clean up expired and already-downloaded uploads.
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

        $downloaded = Upload::whereNotNull('downloaded_at')
            ->where('downloaded_at', '<', now()->subHour())
            ->get();

        foreach ($downloaded as $upload) {
            $this->delete($upload);
            $deleted++;
        }

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

    /**
     * Get the current storage driver.
     */
    public function getStorageDriver(): StorageDriverInterface
    {
        return $this->storage;
    }

    /**
     * Strip path components and NUL bytes from a client filename.
     */
    protected function safeFilename(string $original): string
    {
        $filename = str_replace(["\0", '/', '\\'], '', basename($original));
        $filename = trim($filename);

        if ($filename === '' || $filename === '.' || $filename === '..') {
            return 'file';
        }

        return $filename;
    }
}
