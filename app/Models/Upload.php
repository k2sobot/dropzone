<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Upload extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'filename',
        'path',
        'size',
        'mime_type',
        'uploader_ip',
        'downloaded_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'downloaded_at' => 'datetime',
        ];
    }

    /**
     * Generate a new UUID for the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who uploaded this file.
     */
    public function user()
    {
        return $this->belongsTo( User::class );
    }

    /**
     * Check if the upload has been downloaded.
     */
    public function isDownloaded(): bool
    {
        return $this->downloaded_at !== null;
    }

    /**
     * Check if the upload has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the upload is available for download.
     */
    public function isAvailable(): bool
    {
        return ! $this->isDownloaded() && ! $this->isExpired();
    }

    /**
     * Mark the upload as downloaded.
     */
    public function markAsDownloaded(): void
    {
        $this->update(['downloaded_at' => now()]);
    }

    /**
     * Get the human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Get the download URL.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('download.show', $this->id);
    }
}
