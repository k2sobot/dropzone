<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OAuthProvider extends Model
{
    protected $fillable = [
        'provider',
        'provider_user_id',
        'email',
        'name',
        'avatar',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'is_admin',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get OAuth provider by provider and user ID.
     */
    public static function findByProvider(string $provider, string $providerUserId): ?self
    {
        return static::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();
    }

    /**
     * Get OAuth provider by provider and email.
     */
    public static function findByEmail(string $provider, string $email): ?self
    {
        return static::where('provider', $provider)
            ->where('email', $email)
            ->first();
    }

    /**
     * Get decrypted access token.
     */
    public function getAccessTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted access token.
     */
    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted refresh token.
     */
    public function getRefreshTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted refresh token.
     */
    public function setRefreshTokenAttribute($value): void
    {
        $this->attributes['refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Check if token is expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    /**
     * Grant admin access to this OAuth user.
     */
    public function grantAdmin(): void
    {
        $this->update(['is_admin' => true]);
    }

    /**
     * Revoke admin access from this OAuth user.
     */
    public function revokeAdmin(): void
    {
        $this->update(['is_admin' => false]);
    }
}
