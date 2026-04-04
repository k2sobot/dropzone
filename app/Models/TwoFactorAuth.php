<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuth extends Model
{
    protected $table = 'two_factor_auth';

    protected $fillable = [
        'admin_username',
        'secret',
        'recovery_codes',
        'enabled',
        'enabled_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'enabled_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
        'recovery_codes',
    ];

    /**
     * Get 2FA setup for a username.
     */
    public static function getForUsername(string $username): ?self
    {
        return static::where('admin_username', $username)->first();
    }

    /**
     * Get decrypted secret.
     */
    public function getSecretAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted secret.
     */
    public function setSecretAttribute($value): void
    {
        $this->attributes['secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted recovery codes.
     */
    public function getRecoveryCodesAttribute($value): array
    {
        if (!$value) {
            return [];
        }

        $decrypted = Crypt::decryptString($value);
        return json_decode($decrypted, true) ?? [];
    }

    /**
     * Set encrypted recovery codes.
     */
    public function setRecoveryCodesAttribute(array $codes): void
    {
        $this->attributes['recovery_codes'] = Crypt::encryptString(json_encode($codes));
    }

    /**
     * Generate a new TOTP secret.
     */
    public static function generateSecret(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    /**
     * Generate recovery codes.
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    /**
     * Verify a TOTP code.
     */
    public function verifyCode(string $code): bool
    {
        if (!$this->secret) {
            return false;
        }

        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->secret, $code, window: 2);
    }

    /**
     * Verify and consume a recovery code.
     */
    public function verifyRecoveryCode(string $code): bool
    {
        $codes = $this->recovery_codes;
        $index = array_search(strtoupper($code), $codes);

        if ($index !== false) {
            // Remove used code
            unset($codes[$index]);
            $this->recovery_codes = array_values($codes);
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Enable 2FA.
     */
    public function enable(): void
    {
        $this->update([
            'enabled' => true,
            'enabled_at' => now(),
        ]);
    }

    /**
     * Disable 2FA.
     */
    public function disable(): void
    {
        $this->update([
            'enabled' => false,
            'enabled_at' => null,
        ]);
    }

    /**
     * Check if 2FA is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled && $this->secret;
    }
}
