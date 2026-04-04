<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'max_file_size',
        'max_uploads_per_day',
        'default_expiration',
        'is_active',
        'storage_quota_bytes',
        'storage_used_bytes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole( 'admin' );
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get effective max file size (user-specific or global default).
     */
    public function getMaxFileSizeAttribute( $value ): int
    {
        if ( $value !== null ) {
            return (int) $value;
        }

        return (int) \App\Models\AdminSetting::get( 'max_file_size', 104857600 );
    }

    /**
     * Get effective max uploads per day (user-specific or global default).
     */
    public function getMaxUploadsPerDayAttribute( $value ): int
    {
        if ( $value !== null ) {
            return (int) $value;
        }

        // Default to unlimited (0 means unlimited)
        return 0;
    }

    /**
     * Get effective default expiration (user-specific or global default).
     */
    public function getDefaultExpirationAttribute( $value ): int
    {
        if ( $value !== null ) {
            return (int) $value;
        }

        return (int) \App\Models\AdminSetting::get( 'default_expiration', 24 );
    }

    /**
     * Check if user has storage quota.
     */
    public function hasStorageQuota(): bool
    {
        return $this->storage_quota_bytes !== null && $this->storage_quota_bytes > 0;
    }

    /**
     * Get remaining storage in bytes.
     */
    public function getRemainingStorage(): int
    {
        if ( ! $this->hasStorageQuota() ) {
            return PHP_INT_MAX; // Unlimited
        }

        return max( 0, $this->storage_quota_bytes - $this->storage_used_bytes );
    }

    /**
     * Get storage usage percentage.
     */
    public function getStorageUsagePercent(): int
    {
        if ( ! $this->hasStorageQuota() || $this->storage_quota_bytes === 0 ) {
            return 0;
        }

        return (int) round( ( $this->storage_used_bytes / $this->storage_quota_bytes ) * 100 );
    }

    /**
     * Add to storage used.
     */
    public function addStorageUsed( int $bytes ): void
    {
        $this->increment( 'storage_used_bytes', $bytes );
    }

    /**
     * Subtract from storage used.
     */
    public function subtractStorageUsed( int $bytes ): void
    {
        $this->decrement( 'storage_used_bytes', min( $bytes, $this->storage_used_bytes ) );
    }

    /**
     * Get user's uploads.
     */
    public function uploads()
    {
        return $this->hasMany( Upload::class );
    }
}
