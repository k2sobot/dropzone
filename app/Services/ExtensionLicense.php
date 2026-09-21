<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Carbon;

class ExtensionLicense
{
    public const PREFIX = 'DZ1';

    public static function catalog(): array
    {
        return config('extensions.official', []);
    }

    public static function isPaid(string $package): bool
    {
        return (bool) (self::catalog()[$package]['paid'] ?? false);
    }

    public static function secret(): string
    {
        return (string) config('app.extension_license_key', config('app.key'));
    }

    public static function issue(string $package, string $domain = '*', int $updateYears = 1): string
    {
        $payload = [
            'p' => $package,
            'd' => $domain,
            'e' => now()->addYears($updateYears)->timestamp,
        ];
        $body = self::b64(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $sig = hash_hmac('sha256', $body, self::secret());

        return self::PREFIX.'.'.$body.'.'.$sig;
    }

    public static function decode(string $key): ?array
    {
        $parts = explode('.', trim($key), 3);
        if (count($parts) !== 3 || $parts[0] !== self::PREFIX || $parts[1] === '' || $parts[2] === '') {
            return null;
        }

        [$prefix, $body, $sig] = $parts;
        $expected = hash_hmac('sha256', $body, self::secret());
        if (! hash_equals($expected, $sig)) {
            return null;
        }

        $json = json_decode(self::unb64($body), true);
        if (! is_array($json) || empty($json['p'])) {
            return null;
        }

        return $json;
    }

    public static function storedKey(string $package): string
    {
        return trim((string) AdminSetting::get('extension_license_'.$package, ''));
    }

    /**
     * Lifetime use if the signature matches. `e` is updates-included-until (FreeScout-style).
     */
    public static function isActive(string $package): bool
    {
        if (! self::isPaid($package)) {
            return true;
        }

        $decoded = self::decode(self::storedKey($package));
        if (! $decoded || ($decoded['p'] ?? '') !== $package) {
            return false;
        }

        $domain = (string) ($decoded['d'] ?? '*');
        if ($domain !== '*' && ! self::domainMatches($domain)) {
            return false;
        }

        return true;
    }

    public static function updatesUntil(string $package): ?Carbon
    {
        $decoded = self::decode(self::storedKey($package));
        if (! $decoded || empty($decoded['e'])) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $decoded['e']);
    }

    public static function activate(string $package, string $key): bool
    {
        $decoded = self::decode($key);
        if (! $decoded || ($decoded['p'] ?? '') !== $package) {
            return false;
        }

        $domain = (string) ($decoded['d'] ?? '*');
        if ($domain !== '*' && ! self::domainMatches($domain)) {
            return false;
        }

        AdminSetting::set('extension_license_'.$package, trim($key));

        return true;
    }

    public static function deactivate(string $package): void
    {
        AdminSetting::set('extension_license_'.$package, '');
    }

    protected static function domainMatches(string $licensed): bool
    {
        $host = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $licensed = strtolower($licensed);
        if ($host === '' || $licensed === '') {
            return false;
        }
        if ($licensed[0] === '.') {
            return str_ends_with($host, $licensed) || $host === ltrim($licensed, '.');
        }

        return hash_equals($licensed, $host);
    }

    protected static function b64(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    protected static function unb64(string $b64): string
    {
        $padded = strtr($b64, '-_', '+/');
        $pad = strlen($padded) % 4;
        if ($pad) {
            $padded .= str_repeat('=', 4 - $pad);
        }

        return (string) base64_decode($padded, true);
    }
}
