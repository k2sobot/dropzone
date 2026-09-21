<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Remember-me aligned with Laravel/FreeScout:
 * checkbox name "remember", 400-day httponly cookie, token + password fingerprint
 * so a password change invalidates it. Token is not rotated on each request.
 */
class AdminSession
{
    public const COOKIE = 'dropzone_remember';

    // Same duration as Laravel SessionGuard / FreeScout (400 days).
    public const REMEMBER_MINUTES = 576000;

    public static function login(Request $request, string $username, bool $remember, array $extra = []): void
    {
        $request->session()->regenerate();

        session(array_merge([
            'admin_authenticated' => true,
            'admin_username' => $username,
            'admin_login_time' => time(),
            'admin_remembered' => $remember,
        ], $extra));

        if ($remember) {
            self::issueRememberCookie($request);
        } else {
            self::invalidateRemember();
        }
    }

    public static function restore(Request $request): bool
    {
        $raw = (string) $request->cookie(self::COOKIE);
        if ($raw === '' || ! str_contains($raw, '|')) {
            return false;
        }

        [$token, $passwordFp] = explode('|', $raw, 2);
        $hash = AdminSetting::get('admin_remember_hash');
        $currentFp = self::passwordFingerprint();

        if ($token === '' || ! $hash || ! hash_equals($currentFp, (string) $passwordFp) || ! Hash::check($token, $hash)) {
            return false;
        }

        $username = (string) AdminSetting::get('admin_username', 'admin');

        $request->session()->regenerate();
        session([
            'admin_authenticated' => true,
            'admin_username' => $username,
            'admin_login_time' => time(),
            'admin_remembered' => true,
        ]);

        return true;
    }

    public static function logout(Request $request): void
    {
        self::invalidateRemember();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public static function invalidateRemember(): void
    {
        AdminSetting::set('admin_remember_hash', null);
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    protected static function issueRememberCookie(Request $request): void
    {
        $token = Str::random(64);
        AdminSetting::set('admin_remember_hash', Hash::make($token));

        Cookie::queue(cookie(
            self::COOKIE,
            $token.'|'.self::passwordFingerprint(),
            self::REMEMBER_MINUTES,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax'
        ));
    }

    protected static function passwordFingerprint(): string
    {
        return hash('sha256', (string) AdminSetting::get('admin_password', ''));
    }
}
